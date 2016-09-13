<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorCallable;

/**
 * Obtém dados da memória do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class GetMemoryDataAction extends AbstractABSMonitorCallable
{

    /**
     * Obtém o estado dos canais de um único tipo.
     *
     * @param string $type
     *            Tipo de canal.
     * @return string String com 8 caracteres entre '0' e '1' (1 = Habilitado; 0 = Desabilitado).
     */
    private function getChannelStates(string $type): string
    {
        return strrev($this->modem->getData('modem.channel.' . $type));
    }

    /**
     * Avança o índice de leitura do modem.
     *
     * @return void
     */
    private function nextIndex()
    {
        $blockCount = (int) $this->modem->getData('modem.memory.blockCount');
        $this->modem->setNextIndex(($this->modem->getNextIndex() + 1) % $blockCount);
    }

    /**
     * Processa um bloco de memória registrado com erro.
     *
     * Se o bloco foi recebido pela primeira vez, aguarda alguns minutos e tenta uma nova leitura.
     * Se após aguardar os minutos o bloco continua com erro, avança o índice de leitura.
     *
     * @return void
     */
    private function processCorruptedRecord()
    {
        $index = $this->modem->getNextIndex();

        if ((int) $this->modem->getData('modem.memory.corruptBlock') !== $index) {

            $this->modem->setData('modem.memory.corruptBlock', $index);
            $this->logger->logInfo('data index: %05d corrupted or written pending', $index);

            $this->sleepStage(600); // Próxima execução em 10min.
        } else {

            $this->modem->setData('modem.memory.corruptBlock', - 1);
            $this->logger->logInfo('data index: %05d corrupted on second try, go to next record', $index);

            $this->nextIndex();
        }
    }

    /**
     * Processa um bloco de memória registrado com sucesso.
     *
     * @param int $time
     *            Timestamp de registro.
     * @param int $index
     *            Índice de registro.
     * @param int $status
     *            Status de registro.
     * @param array $values
     *            Valores registrados.
     */
    private function processRecord(int $time, int $index, int $status, array $values)
    {
        $this->logger->logInfo('data index: %05d, data time: %s, data status: %05d', $index,
            date('Y/m/d H:i:s', $time), $status);

        $channels = [
            'A1' => $this->getChannelStates('A1'),
            'A2' => $this->getChannelStates('A2'),
            'PF' => $this->getChannelStates('PF'),
            'PC' => $this->getChannelStates('PC'),
            'TC' => $this->getChannelStates('TC'),
            'TZ' => $this->getChannelStates('TZ')
        ];

        if ($this->manager->addModemData($this->modem, $channels, $index, $status, $time, $values)) {

            $this->logger->logNotice('record index written: %05d', $index);
            $this->nextIndex();
        }
    }

    /**
     * Verifica se o modem esta pronto para leitura.
     *
     * Checa se o configuração de sinal foi definida.
     * Checa se a configuração dos canais foi carregada.
     * Checa se a configuração da memória foi carregada.
     *
     * @return bool
     */
    protected function modemReady(): bool
    {
        return parent::modemReady() && (bool) $this->modem->getData('modem.channels') &&
             (bool) $this->modem->getData('modem.memory');
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorCallable::writeCommand()
     */
    protected function writeCommand(): bool
    {
        if (! $this->modemReady()) {
            return false;
        }

        $blockSize = (int) $this->modem->getData('modem.memory.blockSize');

        $index = $this->modem->getNextIndex();
        $message = $this->modbus->pack23(1, 64205, $blockSize, 64511, $index);

        if (! $this->writeMessage($message)) {
            return false;
        }

        $this->logger->logInfo('loading data index: %05d', $index);
        return true;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorCallable::readResponse()
     */
    protected function readResponse(): bool
    {
        if (! $this->readMessage(1, 23, 133, $response, 'n8v/c*')) {
            return false;
        }

        $data = &$response['data'];
        $time = mktime($data['v4'], $data['v3'], $data['v2'], $data['v6'], $data['v5'], (2000 + $data['v7']));

        // Timestamp inválido indica registro não armazenado ou com erro.
        if ($time === false) {
            $this->processCorruptedRecord();
        } else {
            $this->processRecord($time, $data['v1'], $data['v8'], array_slice($data, 8));
        }

        return true;
    }
}