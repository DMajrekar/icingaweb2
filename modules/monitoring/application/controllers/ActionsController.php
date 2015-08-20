<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

use Icinga\Data\Filter\Filter;
use Icinga\Module\Monitoring\Controller;
use Icinga\Module\Monitoring\Forms\Command\Object\DeleteDowntimesCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ScheduleHostDowntimeCommandForm;
use Icinga\Module\Monitoring\Object\HostList;

/**
 * Monitoring API
 *
 * @method \Icinga\Web\Request getRequest() {
 *     {@inheritdoc}
 *     @return  \Icinga\Web\Request
 * }
 */
class Monitoring_ActionsController extends Controller
{
    /**
     * Schedule host downtimes
     */
    public function scheduleHostDowntimeAction()
    {
        // @TODO(el): Require a filter
        // @TODO(el): $this->backend->list('host')->handleRequest()->fetchAll()
        $hostList = new HostList($this->backend);
        $this->applyRestriction('monitoring/filter/objects', $hostList);
        $hostList->addFilter(Filter::fromQueryString((string) $this->params));
        if (! $hostList->count()) {
            // @TODO(el): Use ApiResponse class for unified response handling.
            $this->getRequest()->sendJson(array(
                'status'    => 'fail',
                'message'   => 'No hosts found matching the given filter'
            ));
        }
        $form = new ScheduleHostDowntimeCommandForm();
        $form
            ->setIsApiTarget(true)
            ->setObjects($hostList->fetch())
            ->handleRequest($this->getRequest());
    }

    /**
     * Remove host downtimes
     */
    public function removeHostDowntimeAction()
    {
        // @TODO(el): Require a filter
        $downtimes = $this->backend
            ->select()
            ->from('downtime', array('host_name', 'id' => 'downtime_internal_id'))
            ->where('object_type', 'host')
            ->applyFilter($this->getRestriction('monitoring/filter/objects'))
            ->handleRequest($this->getRequest())
            ->fetchAll();
        if (empty($downtimes)) {
            // @TODO(el): Use ApiResponse class for unified response handling.
            $this->getRequest()->sendJson(array(
                'status'    => 'fail',
                'message'   => 'No downtimes found matching the given filter'
            ));
        }
        $form = new DeleteDowntimesCommandForm();
        $form
            ->setIsApiTarget(true)
            ->setDowntimes($downtimes)
            ->handleRequest($this->getRequest());
        // @TODO(el): Respond w/ the downtimes deleted instead of the notifiaction added by
        // DeleteDowntimesCommandForm::onSuccess().
    }
}