<?php
// {{{ICINGA_LICENSE_HEADER}}}
// {{{ICINGA_LICENSE_HEADER}}}

namespace Icinga\Module\Monitoring\Form\Setup;

use Icinga\Web\Form;
use Icinga\Web\Form\Element\Note;
use Icinga\Form\Config\Resource\LivestatusResourceForm;

class LivestatusResourcePage extends Form
{
    public function init()
    {
        $this->setName('setup_monitoring_livestatus');
    }

    public function createElements(array $formData)
    {
        $this->addElement(
            'hidden',
            'type',
            array(
                'required'  => true,
                'value'     => 'livestatus'
            )
        );
        $this->addElement(
            new Note(
                'description',
                array(
                    'value' => mt(
                        'monitoring',
                        'Please fill out the connection details below to access the Livestatus'
                        . ' socket interface for your monitoring environment.'
                    )
                )
            )
        );

        if (isset($formData['skip_validation']) && $formData['skip_validation']) {
            $this->addSkipValidationCheckbox();
        } else {
            $this->addElement(
                'hidden',
                'skip_validation',
                array(
                    'required'  => true,
                    'value'     => 0
                )
            );
        }

        $livestatusResourceForm = new LivestatusResourceForm();
        $this->addElements($livestatusResourceForm->createElements($formData)->getElements());
        $this->getElement('name')->setValue('icinga_livestatus');
    }

    public function isValid($data)
    {
        if (false === parent::isValid($data)) {
            return false;
        }

        if (false === isset($data['skip_validation']) || $data['skip_validation'] == 0) {
            if (false === LivestatusResourceForm::isValidResource($this)) {
                $this->addSkipValidationCheckbox();
                return false;
            }
        }

        return true;
    }

    /**
     * Add a checkbox to the form by which the user can skip the connection validation
     */
    protected function addSkipValidationCheckbox()
    {
        $this->addElement(
            'checkbox',
            'skip_validation',
            array(
                'required'      => true,
                'label'         => t('Skip Validation'),
                'description'   => t('Check this to not to validate connectivity with the given Livestatus socket')
            )
        );
    }
}
