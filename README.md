# `Ergontech_Tabular` Data Processor

`ErgonTech_Tabular` (sometimes refered to as "tabular" for the sake of brevity) is a Magento 1.x module designed to make tabular data processing easier. It provides a framework around which data import, export, and more can be built.

## Installation

It is recommended to install `ErgonTech_Tabular` with [composer](http://getcomposer.org/). `composer require ergontech/tabular-magento`.
Currently, there is no [repository](https://getcomposer.org/doc/05-repositories.md) where `Ergontech_Tabular` can be found, so it must be added as a [VCS repo](https://getcomposer.org/doc/05-repositories.md#vcs). As it depends upon [`ergontech/tabular-core`](https://github.com/ergontech/tabular-processor), that repository should also be added.

## Components

Tabular is built using the following:
* **Profile**: Represents a complete action, such as _Import products into Magento from Google Sheet with ID `x`_. Accepts a `Processor` and adds the necessary `Steps` to it to complete the action.
* **Step**: A discrete action, such as _create all nonexistent root categories found in the Rows_ ([example](community/ErgonTech/Tabular/Step/Category/RootCategoryCreator.php))
* **Rows** and **Processor** are described in Tabular's core

## Usage

Out of the box, Tabular provides the following functionality:
* Loading data from Google Sheets
* Importing products, categories, and product categorization using [AvS_FastSimpleImport](http://avstudnitz.github.io/AvS_FastSimpleImport/)
* Importing attributes
* Transforming column headers to match input requirements

## Module Configuration

This module provides the following configuration values:
* `tabular/general/enabled` A yes/no value. Enables/disables the entire module
* `tabular/google_api/type` See [`ErgonTech\Tabular\Model_Source_Google_Api_Type`](community/ErgonTech/Tabular/Model/Source/Google/Api/Type.php)
   * API keys only have access to public sheets, whereas a Service Account can access private sheets
* `tabular/google_api/api_key` Authentication credentials for access. Use the correct authentication data corresponding to the chosen type.

## Profile Extra Data Configuration

The `extra` XML node of a Profile Type configuration in [`config.xml`](community/ErgonTech/Tabular/etc/config.xml) defines the Type-specific data needed by a profile. For example:

* `spreadsheet_id`: For import profiles, this points to a Google Spreadsheet.
* `widget_type`: Select element including the Application's available widget instances

### Input Types

Any `$type` value that can be passed to `Varien_Data_Form_Element_Fieldset::addField` can be used here, _however_, only `text` and `select` are used thus far.

* `text` creates a `<input type="text" />` element
* `select` creates a `<select>  </select>` element
    * Supports both `options` and `source_model` to provide `<option>` elements

### Examples

```xml
<config>
    <global>
        <ergontech>
            <tabular>
                <profile>
                    <type>
                        <foo_bar>
                            <extra>
                                <!--
                                Text input. Will be available to profiles of type `foo_bar` as `$profile->getExtra('baz_value')`
                                -->
                                <baz_value>
                                    <input>text</input>
                                    <label>Baz Value</label>
                                    <comment>Here's where you enter the baz for this profile</comment>
                                </baz_value>

                                <!--
                                Select input with options.
                                  The value stored when "Buzz" is picked in the admin will be `buzz_1`.
                                  It's often useful to store extra data in the `option` node to be used by a profile.
                                -->
                                <buzz_options>
                                    <input>select</input>
                                    <label>Choose a Buzz</label>
                                    <options>
                                        <buzz_1>
                                            <label>Buzz</label>
                                        </buzz_1>
                                        <buzz_2>
                                            <label>Buzzzz</label>
                                        </buzz_2>
                                        <buzz_3>
                                            <label>Buzzzzzzzzzz!!</label>
                                        </buzz_3>
                                    </options>
                                </buzz_options>

                                <!--
                                Select input with source_model
                                 The options available in this select depend on the return of `toOptionArray` of
                                   the `foo_bar/source_fizzy` model.
                                -->
                                <fizzy_choice>
                                    <input>select</input>
                                    <label><![CDATA[Which Fizzy?]]></label>
                                    <source_model>foo_bar/source_fizzy</source_model>
                                </fizzy_choice>
                            </extra>
                        </foo_bar>
                    </type>
                </profile>
            </tabular>
        </ergontech>
    </global>
</config>
```

## Transform Helpers

*Note: The Header Transform functionality of Tabular is deprecated and will be removed in the future!*

Currently, most profile types have both a `ErgonTech\Tabular\HeaderTransformStep` and `ErgonTech\Tabular\RowTransformStep`(see their contents in `ergontech/tabular-core`). As of the pre-1.0 release phase, these both accept just one callback.
