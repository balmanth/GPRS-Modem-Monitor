<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor;

use BCL\System\AbstractObject;
use BCL\System\Logger\LogManager;
use GPRS\System\ModemManagerInterface;
use GPRS\System\ConnectionManager;
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
     * Tipo de modems do monitor.
     *
     * @var array
     */
    private $type;

    /**
     * Instância do gerenciador de modems.
     *
     * @var ModemManagerInterface
     */
    private $modems;

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
     * Lista de modems do monitor.
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
     * Lista de gerenciadores de ações para as etapas de monitoramento.
     *
     * @var array
     */
    private $listeners;

    /**
     * Atualiza a lista de modems monitorados.
     *
     * @return int Quantidade de novos modems carregados.
     */
    private function updateModemList(): int
    {
        $total = 0;
        $response = $this->modems->loadModems($this->type);

        foreach ($response as &$data) {

            $id = $data['id'];

            if (! isset($this->modemEntities[$id])) {

                $this->modemStages[$id] = 0;
                $this->modemEntities[$id] = new ModemEntity($data, $this->modemStages[$id]);

                ++ $total;
            }
        }

        return $total;
    }

    /**
     * Atualiza as informações necessárias da API.
     *
     * @return void
     */
    private function update()
    {
        if ((time() - $this->lastUpdate) > self::API_UPDATE_TIME) {

            if (($total = $this->updateModemList()) > 0) {
                $this->logger->logInfo('%d loaded modems', $total);
            }

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
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\MonitorInterface::__construct($modems, $logger, $connections)
     */
    public function __construct(ModemManagerInterface $modems, LogManager $logger, ConnectionManager $connections,
        int $type)
    {
        $this->type = $type;
        $this->modems = $modems;
        $this->logger = $logger;
        $this->connections = $connections;
        $this->actived = true;
        $this->lastUpdate = 0;
        $this->modemEntities = [];
        $this->modemStages = [];
        $this->listeners = [];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\MonitorInterface::active()
     */
    public function active()
    {
        $this->actived = true;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\MonitorInterface::deactive()
     */
    public function deactive()
    {
        $this->actived = false;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\MonitorInterface::monitore()
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

            if (($connection = $this->connections->get($modemEntity->getHost(), $modemEntity->getPort())) === NULL) {
                continue;
            }

            // O servidor 'gateway' da ABS parece não suportar um número grande de solicitações simultâneas
            // O monitor irá aguardar alguns segundos para dar continuidade ao processo.
            sleep(1);

            $this->logger->setModem($modemEntity);
            $this->logger->setConnection($connection);

            // OBS: A ação pode ser armazenada no modem, evitando criar várias instâncias desnecessárias.
            $action = new MonitorStageAction($this, $this->logger, $modemEntity, $connection);
            $listenerIndex = $listenerKeys[($this->modemStages[$id] % $listenerCount)];

            $modemEntity->setMaxStage($listenerCount);
            $this->listeners[$listenerIndex]->execute($action);

            $this->logger->setModem(NULL);
            $this->logger->setConnection(NULL);
        }
    }
}