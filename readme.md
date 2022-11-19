# ACF Improved - WordPress plugin

A standalone WordPress plugin that replaces some functionality of Advanced Custom Fields with the aim of reducing database queries. This plugin and it's developers have no ties whatsoever to Advanced Custom Fields itself.

## How to use
Replace get_field calls for options groups like this:
> get_field("my_custom_field_group", "option");

With the following:
> use \ACFImproved\Data;
> Data::get_option("my_custom_field_group");

Note: You only need the "use" statement once per file.