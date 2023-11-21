<?php
$CONFIG = [
  // For app "theming" *any* reference of the class path in the list
  // is removed from *any* section
  'appinfo.disable.classes' => [
    'theming' => [
      'OCA\\Theming\\Settings\\PersonalSection',
    ],
  ],
  'appinfo.disable.navigations' => [
    // For app "theming" *any* reference of the route is removed from the
    // <navigations> config
    'dashboard' => [
      'dashboard.dashboard.index',
    ],
  ],
];
