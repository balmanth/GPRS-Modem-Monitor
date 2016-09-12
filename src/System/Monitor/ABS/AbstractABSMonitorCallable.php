<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS;

use BCL\System\Streams\Network\ClientStream;
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
abstract class AbstractABSMonitorCallable extends AbstractMonitorCallable
{

    /**
     * Quantidade máxima de tentativas de recebimento de uma mensagem Modbus.
     *
     * @var int
     */
    const MAX_READING_RETRY = 60;

    /**
     * Quantidade máxima de tentativas de escrita de uma mensagem Modbus.
     *
     * @var int
     */
    const MAX_WRITING_RETRY = 60;

    /**
     * Instância da conexão com o modem.
     *
     * @var ClientStream
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
     * @param int $maxTry
     *            Quantidade máxima de tentativas da operação.
     * @return void
     */
    private function updateRetry(string $operation, int $maxTry)
    {
        $configKey = 'response.' . $operation . '.try';
        $retries = (int) $this->modem->getData($configKey);

        if ($retries < $maxTry) {
            ++ $retries;
        } else {

            $retries = 0;

            $this->logger->logNotice('max of retries for %s, go to next stage', $operation);
            $this->modem->nextStage();
        }

        $this->modem->setData($configKey, $retries);
    }

    /**
     * Tenta enviar dados ao modem.
     *
     * @return void
     */
    private function tryWrite()
    {
        if (! $this->connection->canWrite()) {
            $this->updateRetry('write', self::MAX_WRITING_RETRY);
            return;
        }

        if (! $this->writeCommand()) {
            $this->modem->nextStage();
            return;
        }

        $this->modem->setStageData('response.waiting', true);
    }

    /**
     * Tenta receber e processar dados do modem.
     *
     * @return void
     */
    private function tryRead()
    {
        if (! $this->connection->canRead()) {
            $this->updateRetry('read', self::MAX_READING_RETRY);
            return;
        }

        if (! $this->readResponse()) {
            $this->sleepModem(10);
            return;
        }

        $this->modem->setStageData('response.waiting', false);
        $this->modem->nextStage();
    }

    /**
     * Checa se o configuração de sinal foi definida.
     *
     * @return bool
     */
    protected function modemReady(): bool
    {
        return (bool) $this->modem->getData('modem.signal');
    }

    /**
     * Envia uma mensagem para o modem.
     *
     * @param string $message
     *            Mensagem modbus.
     * @return bool True quando a mensagem foi enviada.
     *         False quando contrário.
     */
    protected function writeMessage(string &$message): bool
    {
        $length = strlen($message);
        $bytes = $this->connection->write($message);

        if ($bytes === $length) {

            $this->logger->logData($message);
            $this->logger->logWrite('success');

            return true;
        }

        if ($bytes > 0) {
            $this->logger->logWrite('failed on write %d of %d bytes, try next', $bytes, $length);
        } else {
            $this->logger->logWrite('failed, try next');
        }

        return false;
    }

    /**
     * Recebe e processa uma mensagem do modem.
     *
     * @param int $address
     *            Endereço de rede esperado.
     * @param int $func
     *            Código da função esperada.
     * @param int $length
     *            Comprimento da mensagem.
     * @param mixed $response
     *            Array com os dados da mensagem (Atualizado por referência
     * @param string $mask
     *            Máscara de formatação das informações de resposta.
     * @return bool True quando uma resposta válida foi recebida.
     *         False quando contrário.
     */
    protected function readMessage(int $address, int $func, int $length, &$response, string $mask = NULL): bool
    {
        $message = $this->connection->read($length);

        if (isset($message{($length - 1)})) {

            $this->logger->logData($message);

            $response = $this->modbus->unpack($message, $address, $func, $mask);
            $this->logger->logRead('success');

            return true;
        }

        $this->logger->logRead('failed on read %d of %d bytes, try again', strlen($message), $length);
        return false;
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
    protected function execute()
    {
        if (! (bool) $this->modem->getStageData('response.waiting')) {
            $this->tryWrite();
        } else {
            $this->tryRead();
        }
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
        $this->modem = $action->getModem();
        $this->connection = $action->getConnection();

        parent::__invoke($action);
    }
}