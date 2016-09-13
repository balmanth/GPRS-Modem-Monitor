<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor;

use BCL\System\Actions\AbstractAction;
use BCL\System\Streams\Network\ClientStream;
use GPRS\System\ModemManagerInterface;
use GPRS\System\LogManager;
use GPRS\System\Entities\ModemEntity;

/**
 * Ação disparada pelo gerenciador de ações para notificar uma etapa de monitoramento.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor
 */
final class MonitorStageAction extends AbstractAction
{

    /**
     * Instância da entidade com informações do modem.
     *
     * @var ModemEntity
     */
    private $modemEntity;

    /**
     * Instância do stream de conexão.
     *
     * @var ClientStream
     */
    private $connection;

    /**
     * Constrtuor.
     *
     * @param AbstractMonitorBase $monitor
     *            Instância do monitor do modem.
     * @param ModemEntity $modemEntity
     *            Instância da entidade com informações do modem.
     * @param ClientStream $connection
     *            Instância do stream de conexão.
     */
    public function __construct(AbstractMonitorBase $monitor, ModemEntity $modemEntity, ClientStream $connection)
    {
        parent::__construct($monitor);

        $this->modemEntity = $modemEntity;
        $this->connection = $connection;
    }

    /**
     * Obtém a instância do gerenciador de modems.
     *
     * @return ModemManagerInterface
     */
    public function getModemManager(): ModemManagerInterface
    {
        return $this->actionSender->getModemManager();
    }

    /**
     * Obtém a instância do gerenciador de registros.
     *
     * @return LogManager
     */
    public function getLogger(): LogManager
    {
        return $this->actionSender->getLogger();
    }

    /**
     * Obtém a instância da entidade com informações do modem.
     *
     * @return ModemEntity
     */
    public function getModemEntity(): ModemEntity
    {
        return $this->modemEntity;
    }

    /**
     * Obtém a instância do stream de conexão com o modem.
     *
     * @return ClientStream
     */
    public function getConnection(): ClientStream
    {
        return $this->connection;
    }
}