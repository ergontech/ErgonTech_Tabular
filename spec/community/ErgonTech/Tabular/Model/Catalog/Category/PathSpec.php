<?php

namespace spec\ErgonTech\Tabular;

use ErgonTech\Tabular\Exception_Profile as ProfileException;
use ErgonTech\Tabular\Model\Resource_Profile_Collection;
use ErgonTech\Tabular\Model_Profile;
use ErgonTech\Tabular\Model\Resource_Profile;
use ErgonTech\Tabular\Model_Source_Profile_Type;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Model_Catalog_Category_PathSpec extends ObjectBehavior
{
    private $profileType;

    public function let(
        \Mage_Core_Model_Config $config,
        Model_Source_Profile_Type $profileType,
        \Mage_Catalog_Model_Category $category,
        \AvS_FastSimpleImport_Model_Import_Entity_Category_Product $categorizationImport,
        Model_Profile $profile
    ){
        $refMage = new \ReflectionClass(\Mage::class);
        $refConfig = $refMage->getProperty('_config');
        $refConfig->setAccessible(true);
        $refConfig->setValue($refMage, $config->getWrappedObject());

        $this->profileType = $profileType;

        $config->getModelInstance('ergontech_tabular/source_profile_type', [])
            ->willReturn($profileType);
        $config->getModelInstance('ergontech_tabular/profile', [])
            ->willReturn($profile);

        $config->getModelInstance('catalog/category', [])
            ->willReturn($category);

        $category->loadByAttribute(Argument::type('string'), Argument::any())
            ->willReturn($category);

        $category->load(Argument::type('string'))
            ->willReturn($category);

        $category->load(Argument::type('numeric'))
            ->willReturn($category);

        $config->getModelInstance('fastsimpleimport/import_entity_category_product', [])
            ->willReturn($categorizationImport);

        $refCatsWithRoots = new \ReflectionProperty($categorizationImport->getWrappedObject(), '_categoriesWithRoots');
        $refCatsWithRoots->setAccessible(true);

        $refCatsWithRoots->setValue($categorizationImport->getWrappedObject(), [
            'foo' => [
                'bar/baz' => [
                    'entity_id' => 1
                ]
            ]
        ]);

        $profile->load(Argument::type('string'), 'name')
            ->willReturn($profile);

        $profileType->getProfileTypes()
            ->willReturn([]);
    }

    public function letGo()
    {
        \Mage::reset();
    }

    function it_can_load_by_name_path()
    {
        $this->load('foo/bar/baz', 'name_path')->shouldReturnAnInstanceOf(\Mage_Catalog_Model_Category::class);
    }
}
