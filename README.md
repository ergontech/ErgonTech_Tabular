# `Ergontech_Tabular` Data Processor

`ErgonTech_Tabular` (sometimes refered to as "tabular" for the sake of brevity) is a Magento 1.x module designed to make tabular data processing easier. It provides a framework around which data import, export, and more can be built.

## Installation

It is recommended to install `ErgonTech_Tabular` with [composer](http://getcomposer.org/). `composer require ergontech/tabular-magento`.
Currently, there is no [repository](https://getcomposer.org/doc/05-repositories.md) where `Ergontech_Tabular` can be found, so it must be added as a [VCS repo](https://getcomposer.org/doc/05-repositories.md#vcs). As it depends upon [`ergontech/tabular-core`](https://github.com/ergontech/tabular-processor), that repository should also be added.

## Conceptually...

Tabular is built similarly to a PHP middleware (and was heavily influenced by [Slim's implementation](http://www.slimframework.com/docs/concepts/middleware.html)) in that it achieves an end goal by executing a series of `Steps`, with each Step executing the next one. In this way, one Step can load data from a source, the next step can  transform that data for import, and the final step can save that data to Magento.

### Components

Tabular is built using the following:
* **Rows**: Represents data passed into and out of Steps.
* **Profile**: Represents a complete action, such as _Import products into Magento from Google Sheet with ID `x`_. Accepts a `Processor` and adds the necessary `Steps` to it to complete the action.
* **Step**: A discrete action, such as _create all nonexistent root categories found in the Rows_ ([example](community/ErgonTech/Tabular/Step/Category/RootCategoryCreator.php))
* **Processor** Container for steps

## Usage

Out of the box, Tabular provides the following functionality:
* Loading data from Google Sheets
* Importing products, categories, and product categorization using [AvS_FastSimpleImport](http://avstudnitz.github.io/AvS_FastSimpleImport/)
* Importing attributes
* Transforming column headers to match input requirements

### Creating a Profile

1. Create a class `implement`-ing [`ErgonTech_Tabular_Model_Profile_Type`](community/ErgonTech/Tabular/Model/Profile/Type.php)
2. Pay close attention to other Profiles, such as [Product import](community/ErgonTech/Tabular/Model/Profile/Type/Product/Import.php)
    * Please add a spec for your class!!
3. Add the necessary [configuration](community/ErgonTech/Tabular/etc/config.xml). See the nodes in `global/ergontech/tabular/profile/type`.
