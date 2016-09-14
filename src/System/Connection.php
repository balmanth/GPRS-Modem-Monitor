<?php
declare(strict_types = 1);
namespace GPRS\System;

use BCL\System\AbstractComponent;
use BCL\System\AbstractException;
use BCL\System\Streams\Network\ClientStream;
use GPRS\System\Entities\ModemEntity;

/**
 * Item de conexão com um servidor externo (gateway) de gestão de modems.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System
 */
final class Connection extends AbstractComponent
{

    /**
     * Tempo limite para estabelecer uma conexão.
     *
     * @var int
     */
    const CONNECTION_TIME = 10;

    /**
     * Tempo limite para enviar ou receber uma resposta.
     *
     * @var int
     */
    const COMMUNICATION_TIME = 600;

    /**
     * Instância do gerenciador de registros.
     *
     * @var LogManager
     */
    private $logger;

    /**
     * Instância do stream de conexão.
     *
     * @var ClientStream
     */
    private $stream;

    /**
     * Especifica se a conexão esta pendente.
     *
     * @var bool
     */
    private $pending;

    /**
     * Tempo do modo de espera da conexão.
     *
     * @var int
     */
    private $waitTime;

    /**
     * Inicia uma tentativa de conexão.
     *
     * @return void
     */
    private function connect()
    {
        $this->logger->setConnection($this);

        try {
            $this->stream->connect();
            sleep(2);
        } catch (AbstractException $exception) {

            $this->waitTime = (time() + 300);
            $this->logger->logException($exception);
        }

        $this->logger->setConnection(NULL);
    }

    /**
     * Tenta restabelecer a conexão.
     *
     * @return void
     */
    private function reconnect()
    {
        if ($this->stream->hasTimedout()) {
            $this->logger->logConnection('communication timeout');
        }

        $this->logger->logNotice('trying new connection');
        $this->connect();
    }

    /**
     * Construtor.
     *
     * @param LogManager $logger
     *            Instância do gerenciador de registros.
     * @param ModemEntity $modem
     *            Instância da entidade com informações do modem.
     */
    public function __construct(LogManager $logger, ModemEntity $modem)
    {
        parent::__construct();

        $mode = ClientStream::PROTOCOL_TCP | ClientStream::ASYNC_MODE | ClientStream::ACCESS_ALL;
        $host = $modem->getHost();
        $port = $modem->getPort();

        $this->logger = $logger;
        $this->componentId = sprintf('%s:%s', $host, $port);
        $this->pending = true;
        $this->waitTime = 0;

        $this->stream = new ClientStream($mode, $host, $port);

        $this->stream->setConnectTimeout(self::CONNECTION_TIME);
        $this->stream->setWriteTimeout(self::COMMUNICATION_TIME);
        $this->stream->setReadTimeout(self::COMMUNICATION_TIME);

        $this->connect();
    }

    /**
     * Envia uma mensagem para o gateway do modem.
     *
     * @param string $message
     *            Mensagem do comando Modbus.
     * @return bool True quando a mensagem foi enviada.
     *         False quando contrário.
     */
    public function writeMessage(string $message): bool
    {
        $length = strlen($message);
        $bytes = $this->stream->write($message);

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
     * Recebe e processa uma mensagem do gateway modem.
     *
     * @param string $message
     *            Mensagem de resposta Modbus. (Atualizado por referência).
     * @param int $length
     *            Comprimento esperado para mensagem.
     * @return bool True quando uma resposta válida foi recebida.
     *         False quando contrário.
     */
    public function readMessage(string &$message, int $length): bool
    {
        $message = $this->stream->read($length);

        if (isset($message{($length - 1)})) {

            $this->logger->logData($message);
            $this->logger->logRead('success');

            return true;
        }

        $this->logger->logRead('failed on read %d of %d expected bytes, try again', strlen($message), $length);
        return false;
    }

    /**
     * Verifica se a conexão esta pronta para uso.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        if ($this->stream->hasConnection()) {

            if ($this->pending) {

                $this->pending = false;
                $this->logger->logConnection('established');
            }

            return true;
        }

        if (! $this->pending) {

            $this->pending = true;
            $this->reconnect();
        }

        return false;
    }

    /**
     * Verifica se a conexão esta em modo de espera.
     *
     * @return bool
     */
    public function isHolding(): bool
    {
        return (time() <= $this->waitTime);
    }

    /**
     * Verifica se a conexão pode enviar dados.
     *
     * @return bool
     */
    public function canWrite(): bool
    {
        return $this->stream->canWrite();
    }

    /**
     * Verifica se a conexão pode receber dados.
     *
     * @return bool
     */
    public function canRead(): bool
    {
        return $this->stream->canRead();
    }

    /**
     * Obtém o endereço da coenxão.
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->componentId;
    }
}