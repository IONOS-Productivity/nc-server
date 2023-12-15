<?php
$CONFIG = [
  // For app "settings" *any* reference of the class path in the list
  // is removed from *any* section
  'appinfo.disable.classes' => [
    'settings' => [
      'OCA\\Settings\\Sections\\Personal\\Availability',
      'OCA\\Settings\\Sections\\Personal\\PersonalInfo',
      'OCA\\Settings\\Settings\\Personal\\Security\\WebAuthn',
      'OCA\\Settings\\Settings\\Personal\\Security\\Password',
      'OCA\\Settings\\Settings\\Personal\\Security\\TwoFactor',
    ],
    'federatedfilesharing' => [
      'OCA\\FederatedFileSharing\\Settings\\PersonalSection',
    ],
  ],
];
