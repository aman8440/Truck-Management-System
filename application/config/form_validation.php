<?php

$config = [
  'signup' => [
    [
      'field' => 'name',
      'label' => 'Name',
      'rules' => 'required'
    ],
    [
      'field' => 'phone',
      'label' => 'Contact Number',
      'rules' => 'required|numeric|trim|min_length[10]|max_length[10]|is_unique[Users.phone]'
    ],
    [
      'field' => 'email',
      'label' => 'Email',
      'rules' => 'required|valid_email|trim|is_unique[Users.email]'
    ],
    [
      'field' => 'gender',
      'label' => 'Gender',
      'rules' => 'required|trim'
    ],
    [
      'field' => 'password',
      'label' => 'Password',
      'rules' => 'required|min_length[8]|trim|matches[passconf]|callback_is_password_strong'
    ],
    [
      'field' => 'passconf',
      'label' => 'Password Confirmation',
      'rules' => 'required|min_length[8]|trim|callback_is_password_strong'
    ]
  ],
  'update' => [
    [
      'field' => 'name',
      'label' => 'Name',
      'rules' => 'required'
    ],
    [
      'field' => 'phone',
      'label' => 'Contact Number',
      'rules' => 'required|numeric|trim|min_length[10]|max_length[10]'
    ],
    [
      'field' => 'email',
      'label' => 'Email',
      'rules' => 'required|valid_email|trim'
    ],
    [
      'field' => 'gender',
      'label' => 'Gender',
      'rules' => 'required|trim'
    ],
  ]
];

$config['error_prefix']='<div class="error">*';
$config['error_suffix']='</div>';
