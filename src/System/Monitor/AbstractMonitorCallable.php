<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor;

use BCL\System\AbstractObject;
use BCL\System\Logger\LogManager;
use GPRS\System\Entities\ModemEntity;

/**
 * Contêm os métodos e propriedades basicas para um callable de processamento de etapas de monitoramento.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor
 */
abstract class AbstractMonitorCallable extends AbstractObject
{

    /**
     * Instância do gerenciador de registros.
     *
     * @var LogManager
     */
    protected $logger;

    /**
     * Instância com as informações do modem.
     *
     * @var ModemEntity
     */
    protected $modem;

    /**
     * Executa a ação.
     *
     * @return void
     */
    abstract protected function execute();

    /**
     * Verifica se o modem esta em espera.
     *
     * @return bool
     */
    private function waitModem(): bool
    {
        if (time() < (int) $this->modem->getData('monitor.waitTime')) {
            return true;
        } else
            if ((bool) $this->modem->getData('monitor.sleeping') === true) {

                $this->modem->setData('monitor.sleeping', false);
                $this->logger->logNotice('modem wake up');
            }

        return false;
    }

    /**
     * Verifica s a etapa atual esta em espera.
     *
     * @return bool
     */
    private function waitStage(): bool
    {
        if (time() < (int) $this->modem->getStageData('monitor.waitTime')) {

            $this->modem->nextStage();
            return true;
        } else
            if ((bool) $this->modem->getStageData('monitor.sleeping') === true) {

                $this->modem->setStageData('monitor.sleeping', false);
                $this->logger->logNotice('stage wake up');
            }

        return false;
    }

    /**
     * Coloca o modem em espera.
     *
     * @param int $seconds
     *            Tempo em segundos.
     * @param bool $log
     *            Defina True para registrar o modo de espera ou False para não registrar.
     * @return void
     */
    protected function sleepModem(int $seconds, bool $log = true)
    {
        $waitTime = (time() + $seconds);
        $this->modem->setData('monitor.waitTime', $waitTime);

        if ($log) {

            $this->modem->setData('monitor.sleeping', true);
            $this->logger->logNotice('modem wait to: %s, %d seconds', date('Y/m/d H:i:s', $waitTime), $seconds);
        }
    }

    /**
     * Coloca a etapa atual em espera.
     *
     * @param int $seconds
     *            Tempo em segundos.
     * @param bool $log
     *            Defina True para registrar o modo de espera ou False para não registrar.
     * @return void
     */
    protected function sleepStage(int $seconds, bool $log = true)
    {
        $waitTime = (time() + $seconds);
        $this->modem->setStageData('monitor.waitTime', $waitTime);

        if ($log) {

            $this->modem->setStageData('monitor.sleeping', true);
            $this->logger->logNotice('next stage after: %s', date('Y/m/d H:i:s', $waitTime));
        }
    }

    /**
     * Quando a objeto callable é executado.
     * Inicia as configurações para execução da ação.
     *
     * @param MonitorStageAction $action
     *            Instância com informações da ação.
     * @return void
     */
    public function __invoke(MonitorStageAction $action)
    {
        $this->modem = $action->getModem();
        $this->logger = $action->getLogger();

        if (! $this->waitStage() && ! $this->waitModem()) {

            try {
                $this->execute();
            } catch (\Exception $exception) {
                $this->logger->logException($exception);
            }
        }
    }
}