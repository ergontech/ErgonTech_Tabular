# Tabular Configuration XML

## Profile Extra Data Configuration XML

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
