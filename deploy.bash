#!/bin/bash
sed -n "/``bookmark``/,/``bookmark``/p" template.php > template-widget.php
