<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Obtém dados da memória do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Common\Actions
 */
abstract class AbstractGetMemoryDataAction extends AbstractABSMonitorAction
{

    /**
     * Monta a mensagem para caregar um bloco de dados da memória.
     *
     * @return string
     */
    private function packLoadMessage(): string
    {
        $blockSize = (int) $this->modem->getData('modem.memory.blockSize');
        $index = $this->modem->getNextIndex();

        return $this->modbus->pack23(1, $this->getReadAddress(), $blockSize, $this->getWriteAddress(), $index);
    }

    /**
     * Obtém os estados dos canais de um único tipo.
     *
     * @param string $type
     *            Tipo de canal.
     * @return string String com 8 bytes entre '0' e '1' onde cada byte representa: 1 = Habilitado; 0 = Desabilitado.
     */
    private function getChannelStates(string $type): string
    {
        return strrev($this->modem->getData('modem.channel.' . $type));
    }

    /**
     * Avança o índice de leitura do bloco de memória do modem.
     *
     * @return void
     */
    private function increateMemoryIndex()
    {
        $blockCount = (int) $this->modem->getData('modem.memory.blockCount');
        $this->modem->setNextIndex(($this->modem->getNextIndex() + 1) % $blockCount);
    }

    /**
     * Processa um bloco de memória registrado com dados inválidos.
     *
     * Se o bloco foi recebido pela primeira vez, aguarda alguns minutos e tenta uma nova leitura.
     * Se após aguardar o tempo necessário o bloco continua com erro avança o índice de leitura.
     *
     * @return void
     */
    private function processInvalidRecord()
    {
        $index = $this->modem->getNextIndex();

        if ((int) $this->modem->getData('modem.memory.corruptBlock') !== $index) {

            $this->modem->setData('modem.memory.corruptBlock', $index);
            $this->logger->logInfo('data index: %05d corrupted or written pending', $index);

            $this->sleepStage(600); // Próxima execução em 10min.
        } else {

            $this->modem->setData('modem.memory.corruptBlock', - 1);
            $this->logger->logInfo('data index: %05d corrupted on second try, go to next record', $index);

            $this->increateMemoryIndex();
        }
    }

    /**
     * Processa um bloco de memória registrado com dados válidos.
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
    private function processValidRecord(int $time, int $index, int $status, array &$values)
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
            $this->logger->logInfo('record index: %05d success', $index);
            $this->increateMemoryIndex();
        } else {
            $this->logger->logInfo('record index: %05d failure', $index);
        }
    }

    /**
     * Analisa as informações carregadas do bloco de memória.
     *
     * @param array $data
     *            Dados recebidos do gateway.
     */
    private function parseLoadedData(array &$data)
    {
        $time = mktime($data['v4'], $data['v3'], $data['v2'], $data['v6'], $data['v5'], (2000 + $data['v7']));

        // Timestamp inválido indica registro não armazenado ou com erro.
        if ($time === false) {
            $this->processCorruptedRecord();
        } else {

            $index = $data['v1'];
            $status = $data['v8'];
            $values = array_slice($data, 8);

            $this->processValidRecord($time, $index, $status, $values);
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        if ($this->isReady() && $this->writeMessage($this->packLoadMessage())) {

            $this->logger->logInfo('loading data index: %05d', $this->modem->getNextIndex());
            return true;
        }

        return false;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::readResponse()
     */
    protected function readResponse(): bool
    {
        if ($this->readMessage(1, 23, 133, $response, 'n8v/c*')) {

            $this->parseLoadedData($response['data']);
            return true;
        }

        return false;
    }

    /**
     * Verifica se a ação esta pronta para carregar os dados do bloco de memória.
     *
     * @return bool
     */
    abstract protected function isReady(): bool;

    /**
     * Obtém o endereço de leitura dos dados do bloco de memória.
     *
     * @return int
     */
    abstract protected function getReadAddress(): int;

    /**
     * Obtém o endereço de escrita do índice do bloco de memória desejado.
     *
     * @return int
     */
    abstract protected function getWriteAddress(): int;
}