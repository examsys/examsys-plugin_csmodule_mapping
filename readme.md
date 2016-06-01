Title: plugin_csmodule_mapping
Author: Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
Copyright: University of Nottingham 2016 onwards
Description:

plugin_csmodule_mapping was developed for the University of Nottingham.
It allows Rogō to map module codes from the SATURN student management system
to the CAMPUS SOLUTIONS student management system and vice-versa by calling a web serice
hosted by a CAMPUS SOLUTIONS server.

This is of use when connecting to systems that hold historic data refering to
the SATURN codes when Rogō is referring to them as CAMPUS SOLUTION codes.

Installation:

1. Extract plugin_mapping archive into the plugins/mapping directory inside Rogō.
2. Run plugin_csmodule_mapping/db/install_schema.sql into your Rogō database.
3. Run plugin_csmodule_mapping/db/install_data.sql into your Rogō database.