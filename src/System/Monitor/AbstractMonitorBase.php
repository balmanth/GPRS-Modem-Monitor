<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor;

use BCL\System\AbstractObject;
use BCL\System\Logger\LogManager;
use GPRS\System\ModemManagerInterface;
use GPRS\System\ConnectionManager;
use GPRS\System\Entities\ConversionEntity;
use GPRS\System\Entities\ModemEntity;

/**
 * Contêm os métodos e propriedades base para um monitor de modems.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor
 */
abstract class AbstractMonitorBase extends AbstractObject
{

    /**
     * Tempo para atualização de dados da API.
     *
     * @var int
     */
    const API_UPDATE_TIME = 300;

    /**
     * Identifica os monitores do tipo modem celular ABS.
     *
     * @var int
     */
    const MONITOR_ABS_MODEM = 0x01;

    /**
     * Identifica os monitores do tipo datalogger compacto ABS.
     *
     * @var int
     */
    const MONITOR_ABS_DATALOGGER = 0x02;

    /**
     * Tipo de modems do monitor.
     *
     * @var int
     */
    private $type;

    /**
     * Instância do gerenciador de registros.
     *
     * @var LogManager
     */
    private $logger;

    /**
     * Instância do gerenciador de conexões ativas.
     *
     * @var ConnectionManager
     */
    private $connections;

    /**
     * Lista de manipuladores das informações de conversão dos dados armazenados pelos sensores dos modems.
     *
     * @var array
     */
    private $conversionEntities;

    /**
     * Lista de gerenciadores de ações para as etapas de monitoramento.
     *
     * @var array
     */
    private $listeners;

    /**
     * Especifica se o monitor esta ou não ativado.
     *
     * @var boolean
     */
    private $actived;

    /**
     * Timestamp da última atualização de dados da API.
     *
     * @var int
     */
    private $lastUpdate;

    /**
     * Instância do gerenciador de modems.
     *
     * @var ModemManagerInterface
     */
    private $modemManager;

    /**
     * Lista de manipuladores das informações dos modems.
     *
     * @var array
     */
    private $modemEntities;

    /**
     * Lista de etapas dos modems do monitor.
     *
     * @var array
     */
    private $modemStages;

    /**
     * Atualiza a lista de informações de conversão.
     *
     * @return void
     */
    private function updateConversionList()
    {
        $response = $this->modemManager->loadConversions($this->type);
        $updated = 0;
        $loaded = 0;

        foreach ($response as &$data) {

            $id = $data['id'];

            if (! isset($this->conversionEntities[$id])) {
                ++ $loaded;
                $this->conversionEntities[$id] = new ConversionEntity($data);
            } else {
                ++ $updated;
                $this->conversionEntities[$id]->update($data);
            }
        }

        $this->logger->logInfo('%d loaded and %d updated conversions', $loaded, $updated);
    }

    /**
     * Atualiza a lista de informações dos modems.
     *
     * @return void
     */
    private function updateModemList()
    {
        $response = $this->modemManager->loadModems($this->type);
        $updated = 0;
        $loaded = 0;

        foreach ($response as &$data) {

            $id = $data['id'];

            if (! isset($this->modemEntities[$id])) {

                ++ $loaded;

                $this->modemStages[$id] = 0;
                $this->modemEntities[$id] = new ModemEntity($data, $this->modemStages[$id], $this->conversionEntities);
            } else {

                ++ $updated;
                $this->modemEntities[$id]->update($data, $this->conversionEntities);
            }
        }

        $this->logger->logInfo('%d loaded and %d updated modems', $loaded, $updated);
    }

    /**
     * Atualiza as informações necessárias da API.
     *
     * @return void
     */
    private function update()
    {
        if ((time() - $this->lastUpdate) > self::API_UPDATE_TIME) {

            $this->updateConversionList();
            $this->updateModemList();

            $this->lastUpdate = time();
        }
    }

    /**
     * Adiciona uma ação no monitor.
     *
     * @param int $code
     *            Código da ação.
     * @return MonitorStageListener Instância do gerenciador de eventos.
     * @throws \Exception
     */
    protected function addListener(int $code): MonitorStageListener
    {
        if (isset($this->listeners[$code])) {

            $message = sprintf('Um gerenciador de ações com o código \'%d\' já foi adicionado anteriormente.', $code);
            throw new \Exception($message);
        }

        $listener = new MonitorStageListener();
        $this->listeners[$code] = $listener;

        return $listener;
    }

    /**
     * Construtor.
     *
     * @param ModemManagerInterface $modems
     *            Instância do gerenciador de modems.
     * @param LogManager $logger
     *            Instância do gerenciador de registros.
     * @param ConnectionManager $connections
     *            Instância do gerenciador de conexões.
     * @param int $type
     *            Tipo de modem.
     */
    public function __construct(ModemManagerInterface $modems, LogManager $logger, ConnectionManager $connections,
        int $type)
    {
        $this->type = $type;
        $this->logger = $logger;
        $this->connections = $connections;
        $this->conversionEntities = [];
        $this->listeners = [];
        $this->actived = true;
        $this->lastUpdate = 0;

        $this->modemManager = $modems;
        $this->modemEntities = [];
        $this->modemStages = [];
    }

    /**
     * Ativa o monitor.
     *
     * @return void
     */
    public function active()
    {
        $this->actived = true;
    }

    /**
     * Desativa o monitor.
     *
     * @return void
     */
    public function deactive()
    {
        $this->actived = false;
    }

    /**
     * Monitora os modems da lista de acordo com a etapa atual de cada modem.
     *
     * @return void
     */
    public function monitore()
    {
        if (! $this->actived) {
            return;
        }

        $this->update();

        $listenerKeys = array_keys($this->listeners);
        $listenerCount = count($listenerKeys);

        foreach ($this->modemEntities as $id => $modemEntity) {

            if (($connection = $this->connections->getConnection($modemEntity)) === NULL) {
                continue;
            }

            $this->logger->setModemEntity($modemEntity);
            $this->logger->setConnection($connection);

            // OBS: A ação pode ser armazenada no modem, evitando criar várias instâncias desnecessárias.
            $action = new MonitorStageAction($this, $modemEntity, $connection);
            $listenerIndex = $listenerKeys[($this->modemStages[$id] % $listenerCount)];

            $modemEntity->setMaxStage($listenerCount);
            $this->listeners[$listenerIndex]->execute($action);

            $this->logger->setModemEntity(NULL);
            $this->logger->setConnection(NULL);
        }
    }

    /**
     * Obtém a instância do gerenciador de registros.
     *
     * @return LogManager
     */
    public function getLogger(): LogManager
    {
        return $this->logger;
    }

    /**
     * Obtém a instância do gerenciador de modems.
     *
     * @return ModemManagerInterface
     */
    public function getModemManager(): ModemManagerInterface
    {
        return $this->modemManager;
    }
}