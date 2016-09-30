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

## Adding Functionality

The main entry points for functionality are profile type classes, profile type XML configuration, and Row Transforms.

* [XML Configuration](community/ErgonTech/Tabular/etc/README.md)
* [Profile Type Classes](community/ErgonTech/Tabular/Model/Profile/Type/README.md)
* [Transform Helpers](#transform-helpers)

## Module Configuration

This module provides the following configuration values:
* `tabular/general/enabled` A yes/no value. Enables/disables the entire module
* `tabular/google_api/type` See [`ErgonTech\Tabular\Model_Source_Google_Api_Type`](community/ErgonTech/Tabular/Model/Source/Google/Api/Type.php)
   * API keys only have access to public sheets, whereas a Service Account can access private sheets
* `tabular/google_api/api_key` Authentication credentials for access. Use the correct authentication data corresponding to the chosen type.

## Transform Helpers

*Note: The Header Transform functionality of Tabular is deprecated and will be removed in the future!*

Currently, most profile types have both a `ErgonTech\Tabular\HeaderTransformStep` and `ErgonTech\Tabular\RowTransformStep`(see their contents in `ergontech/tabular-core`). As of the pre-1.0 release phase, these both accept just one callback.

### Example

Most Transform Helpers are picked as an "extra profile data" option. Existing Profile Types use a `callback` node in the option to specify the method to be called:

```xml
<!-- config/global/ergontech/tabular/profile/type/[type] -->
<extra>
    <transform_option>
        <input>select</input>
        <label>Transform Option</label>
        <options>
            <option_1>
                <label>First</label>
                <callback>ClassName_Of_Helper_Class::rowTransformMethodName</callback>
            </option_1>
            <option_2>
                <label>Second</label>
                <callback>ClassName_Of_Helper_Class::otherRowTransformMethodName</callback>
            </option_2>
        </options>
    </transform_option>
</extra>
```

For an example of how to retrieve the callback configuration and turn it into a valid `callable`, see [`ErgonTech\Tabular\Helper_RowTransforms::getRowTransformCallbackForProfile`](community/ErgonTech/Tabular/Helper/RowTransforms#L9-L27)
