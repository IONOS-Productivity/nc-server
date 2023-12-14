<?php
$CONFIG = [
  // For app "settings" *any* reference of the class path in the list
  // is removed from *any* section
  'appinfo.disable.classes' => [
    'settings' => [
      'OCA\\Settings\\Sections\\Personal\\Availability',
    ],
  ],
];
