<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS;

use GPRS\System\Connection;
use GPRS\System\Monitor\AbstractMonitorCallable;
use GPRS\System\Monitor\MonitorStageAction;

/**
 * Contêm os métodos e propriedades basicas para um callable de processamento de etapas de monitoramento (modems ABS).
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS
 */
abstract class AbstractABSMonitorAction extends AbstractMonitorCallable
{

    /**
     * Quantidade máxima de tentativas de recebimento de uma mensagem Modbus.
     *
     * @var int
     */
    const MAX_READING_RETRY = 18;

    /**
     * Quantidade máxima de tentativas de escrita de uma mensagem Modbus.
     *
     * @var int
     */
    const MAX_WRITING_RETRY = 18;

    /**
     * Tempo para o modo de espera das tentativas subsequentes.
     *
     * @var int
     */
    const MAX_RETRY_SLEEP = 5;

    /**
     * Instância da conexão.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Instância do ajudante com o protocolo Modbus.
     *
     * @var ABSModbusProtocol
     */
    protected $modbus;

    /**
     * Atualiza a quantidade máxima de tentativas para a operação.
     *
     * @param string $operation
     *            Nome da operação.
     * @param int $maxtry
     *            Quantidade máxima de tentativas da operação.
     * @return void
     */
    private function updateFailedAttempts(string $operation, int $maxtry)
    {
        $configKey = 'response.' . $operation . '.try';
        $retries = (int) $this->modem->getData($configKey);

        if ($retries < $maxtry) {

            $this->sleepModem(self::MAX_RETRY_SLEEP, false);
            ++ $retries;
        } else {

            $this->logger->logNotice('max of %d retries for %s (%d seconds), go to next stage', $maxtry, $operation,
                (self::MAX_RETRY_SLEEP * $maxtry));

            $retries = 0;

            $this->modem->setStageData('response.waiting', false);

            $this->connection->unlock($this->modem);
            $this->modem->nextStage();
        }

        $this->modem->setData($configKey, $retries);
    }

    /**
     * Tenta enviar dados ao modem.
     *
     * @return bool
     */
    private function tryWriteCommand(): bool
    {
        if (! $this->connection->canWrite()) {

            $this->updateFailedAttempts('write', self::MAX_WRITING_RETRY);
            return false;
        }

        if (! $this->writeCommand()) {

            $this->connection->unlock($this->modem);
            $this->modem->nextStage();

            return false;
        }

        $this->modem->setData('response.write.try', 0);
        $this->modem->setStageData('response.waiting', true);

        return true;
    }

    /**
     * Tenta receber e processar dados do modem.
     *
     * @return bool
     */
    private function tryReadCommand(): bool
    {
        if (! $this->connection->canRead()) {

            $this->updateFailedAttempts('read', self::MAX_READING_RETRY);
            return false;
        }

        if (! $this->readResponse()) {

            $this->sleepModem(10, false);
            return false;
        }

        $this->modem->setData('response.read.try', 0);
        $this->modem->setStageData('response.waiting', false);

        $this->connection->unlock($this->modem);
        $this->modem->nextStage();

        return true;
    }

    /**
     * Envia um comando ao modem.
     *
     * @return bool True quando a mensagem foi enviada.
     *         False quando contrário.
     */
    abstract protected function writeCommand(): bool;

    /**
     * Recebe a resposta do último comando enviado e tentar fazer o processamento.
     *
     * @return bool True quando a mensagem foi recebida e processada.
     *         False quando contrário.
     */
    abstract protected function readResponse(): bool;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractMonitorCallable::execute()
     */
    protected function execute(): bool
    {
        if (! (bool) $this->modem->getStageData('response.waiting')) {
            return $this->tryWriteCommand();
        }

        return $this->tryReadCommand();
    }

    /**
     * Construtor.
     */
    public function __construct()
    {
        $this->modbus = new ABSModbusProtocol();
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractMonitorCallable::__invoke($action)
     */
    public function __invoke(MonitorStageAction $action)
    {
        $this->connection = $action->getConnection();

        parent::__invoke($action);
    }
}