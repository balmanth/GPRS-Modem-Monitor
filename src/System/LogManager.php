<?php
declare(strict_types = 1);
namespace GPRS\System;

use BCL\System\Streams\Network\ClientStream;
use GPRS\System\Entities\ModemEntity;
use GPRS\System\Monitor\AbstractMonitorBase;

/**
 * Gerênciador de objetos para manipulação de registros de atividades.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System
 */
final class LogManager extends \BCL\System\Logger\LogManager
{

    /**
     * Instância da entidade com informações do modem associado ao registro.
     *
     * @var ModemEntity
     */
    private $modemEntity;

    /**
     * Instância da conexão associada ao registro.
     *
     * @var ClientStream
     */
    private $connection;

    /**
     * Instância do monitor associado ao registro.
     *
     * @var AbstractMonitorBase
     */
    private $monitor;

    /**
     * Comando do registro.
     *
     * @var string
     */
    private $command;

    /**
     * Mensagem do registro.
     *
     * @var string
     */
    private $message;

    /**
     * Define a instância da entidade com as informações do modem relacionado ao registro.
     *
     * @param ModemEntity|NULL $modemEntity
     *            Instância do modem ou Null para desassociar o modem atual.
     * @return void
     */
    public function setModemEntity($modemEntity)
    {
        $this->modemEntity = $modemEntity;
    }

    /**
     * Define a instância da conexão associada ao registro.
     *
     * @param ClientStream|NULL $connection
     *            Instância da conexão ou Null para desassociar a conexão atual.
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Define a instância do monitor associado ao registro.
     *
     * @param AbstractMonitorBase|NULL $monitor
     *            Instância do monitor ou Null para desassociar o monitor atual.
     * @return void
     */
    public function setMonitor($monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Envia um registro para os objetos de manipulação de registros.
     *
     * @param mixed ...$params
     *            Parâmetros informativos do registro.
     * @return void
     */
    public function log(...$params)
    {
        $monitor = (isset($this->monitor) ? substr($this->monitor->getClass(), 24) : '-');
        $stage = (isset($this->modemEntity) ? $this->modemEntity->getStage() : '-');
        $address = isset($this->connection) ? $this->connection->getAddress() : '-';
        $message = (isset($this->message) ? vsprintf('\'' . $this->message . '\'', $params) : '');

        parent::log($monitor, $this->command, $stage, $address, $message);
    }

    /**
     * Registra uma atividade de notificação.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logNotice(string $message = NULL, ...$params)
    {
        $this->command = 'NOTICE';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade informativa.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logInfo(string $message = NULL, ...$params)
    {
        $this->command = 'INFO';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade para depuração de dados.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @return void
     */
    public function logData(string $message = NULL)
    {
        $this->command = 'DATA';
        $this->message = strtoupper(implode('', unpack('H*', $message)));

        $this->log();
    }

    /**
     * Registra uma atividade de conexão.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logConnection(string $message = NULL, ...$params)
    {
        $this->command = 'CONNECTION';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade de escrita.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logWrite(string $message = NULL, ...$params)
    {
        $this->command = 'WRITE';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade de leitura.
     *
     * @param string $message
     *            Mensagem descritiva da atividade.
     * @param mixed ...$params
     *            Parâmetros da mensagem.
     * @return void
     */
    public function logRead(string $message = NULL, ...$params)
    {
        $this->command = 'READ';
        $this->message = $message;

        $this->log(...$params);
    }

    /**
     * Registra uma atividade que originou um erro.
     *
     * @param int $code
     *            Código de erro.
     * @param string $operation
     *            Descrição da operação envolvida.
     * @param string $message
     *            Mensagem de erro.
     * @return void
     */
    public function logError(int $code, string $operation, string $message)
    {
        $this->command = 'ERROR';
        $this->message = 'code: %d, operation: "%s" message: "%s"';

        $this->log($code, $operation, $message);
    }

    /**
     * Registra uma atividade que originou uma exceção.
     *
     * @param \Exception $exception
     *            Instância da exceção.
     * @return void
     */
    public function logException(\Exception $exception)
    {
        $this->command = 'EXCEPTION';
        $this->message = 'class: %s, code: %d, message: "%s"';

        $this->log(get_class($exception), $exception->getCode(), $exception->getMessage());
    }
}