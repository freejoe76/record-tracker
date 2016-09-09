#!/bin/bash
# Run this after installation, and after making changes to template.php
# We have this file so we don't have to track the nearly-identical template.php
# and template-widget.php files.
# We have two files because I wanted to be able to test out the markup and php
# outside of wordpress.
sed -n "/``bookmark``/,/``bookmark``/p" template.php > template-widget.php
