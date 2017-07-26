<?php

namespace KskTaxFreeCustomerGroups;

use Doctrine\Common\Cache\CacheProvider;
use Enlight_Event_EventArgs;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Models\Attribute\CustomerGroup;
use Shopware\Models\Customer\Customer;

/**
 * Class KskTaxFreeCustomerGroups
 * @package KskTaxFreeCustomerGroups
 */
class KskTaxFreeCustomerGroups extends Plugin
{
    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        /** @var ModelManager $em */
        $em = $this->container->get('models');
        /** @var CrudService $crud */
        $crud = $this->container->get('shopware_attribute.crud_service');

        $crud->update(
            's_core_customergroups_attributes',
            'ksk_tax_free',
            TypeMapping::TYPE_BOOLEAN,
            [
                'label' => 'Steuerfrei',
                'displayInBackend' => true,
                'custom' => false,
            ],
            null,
            false,
            0
        );

        /** @var CacheProvider $metaDataCache */
        $metaDataCache = $em->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        $em->generateAttributeModels(['s_core_customergroups_attributes']);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_GetUserData_FilterResult' => 'filterUserData',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function filterUserData(Enlight_Event_EventArgs $args)
    {
        /** @var int $userId */
        $userId = (int) $args->get('id');
        /** @var array $userData */
        $userData = $args->getReturn();

        if ($userId === 0) {
            return;
        }

        /** @var ModelManager $em */
        $em = $this->container->get('models');

        /** @var Customer $customer */
        if (!($customer = $em->find(Customer::class, $userId)) instanceof Customer) {
            return;
        }

        $group = $customer->getGroup();

        if (!$group->getAttribute() instanceof CustomerGroup || $group->getAttribute()->getKskTaxFree() != true) {
            return;
        }

        $userData['additional']['countryShipping']['taxfree'] = "1";
        $args->setReturn($userData);
    }
}
