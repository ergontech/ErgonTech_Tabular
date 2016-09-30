# Profile Types

A "Profile Type" takes a [`ErgonTech\Tabular\Model_Profile`](../../../Profile.php) and builds a bundle of Steps that can be executed.

See the [`ErgonTech\Tabular\Helper_Profile_Type_Factory`](../../../../Helper/Profile/Type/Factory.php) class is responsible for automatically generating a [`ErgonTech\Tabular\Model_Profile_Type`](../../Type.php) for a given Profile.

## Existing Profile Type Classes

* [Category Import](../Category/Import.php): Uses AvS_FastSimpleImport's [Category Import](https://avstudnitz.github.io/AvS_FastSimpleImport/categories.html) to pull in Category data from Google Sheets and import into a Magento store.
* [Product Import](../Product/Import.php): Uses AvS_FastSimpleImport's [Product Import](https://avstudnitz.github.io/AvS_FastSimpleImport/products.html) to pull in Product data from Google Sheets and import into a Magento store.
* [Product Categorization](../ProductCategorization.php): Uses AvS_FastSimpleImport's [Category Products](https://avstudnitz.github.io/AvS_FastSimpleImport/category-products.html) to pull in Product Categorization from Google Sheets and import into a Magento store.
* [Entity Import](../Entity/Import.php): A very generic import that is designed to import any entity in Magento that has a "Model" and can be "save"-ed.

## Adding a Profile Type

1. Create a class `implement`-ing [`ErgonTech\Tabular\Model_Profile_Type`](../Type.php)
2. Pay close attention to other Profiles, such as [Product import](Product/Import.php)
    * Please add a spec for your class!!
3. Add the necessary [configuration](../../../etc/config.xml). See the nodes in `global/ergontech/tabular/profile/type`.

Most Importantly, the profile type *must implement* `ErgonTech\Tabular\Model_Profile_Type`. As such, it must implement both `initialize` and `execute`. In order to be a candidate for use with the profile type factory, a node in Magento's configuration must be added that points to your new class:
 ```xml
 <global>
    <ergontech>
        <tabular>
            <profile>
                <type>
                    <name_of_type>
                        <class>ErgonTech\Tabular\Model_Profile_Type_New_One</class>
                    </name_of_type>
                </type>
            </profile>
        </tabular>
    </ergontech>
</global>
```

### Secret expectations
1. A Logger must be registerd with `ErgonTech\Tabular\Helper_Monolog` that matches the `profile_type` of the profile provided in `initialize`. This is because both `ErgonTech_Tabular_Adminhtml_Tabular_ProfileController` and `ErgonTech\Tabular\Command\RunProfileCommand` expect it to be available to show progress and errors.
2. The transform callbacks provided by `ErgonTech\Tabular\Helper_HeaderTransforms` and `ErgonTech\Tabular\Helper_RowTransforms` all **require** that the callback is bound to an instance of `ErgonTech\Tabular\Model_Profile`. Luckily, both helpers provide a method `getXTransformCallbackForProfile` which correctly takes the the `$profile->getExtra('x_transform_callback')` value (see how it's done in the config file!) and gives a valid callback to be passed to `XTransformStep` instances. 