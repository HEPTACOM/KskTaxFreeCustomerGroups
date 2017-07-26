<?php

namespace KskTaxFreeCustomerGroups;

use Doctrine\Common\Cache\CacheProvider;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;

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
}
