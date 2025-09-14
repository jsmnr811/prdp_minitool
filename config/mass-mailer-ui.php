<?php

return [
  /*
    |--------------------------------------------------------------------------
    | UI Framework Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the UI framework and styling for the mass mailer interface.
    | This allows for consistent theming and easy switching between frameworks.
    |
    */

  'framework' => env('MASS_MAILER_FRAMEWORK', 'bootstrap'),

  'frameworks' => [
    'bootstrap' => [
      'name' => 'Bootstrap',
      'version' => '5.3.8',
      'css' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
      'js' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js',
    ],
    'tailwind' => [
      'name' => 'Tailwind CSS',
      'version' => '3.x',
      'css' => null, // Usually included in the main layout
      'js' => null,
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | External Libraries Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external libraries like Quill editor and SweetAlert.
    | Define CDN links, versions, and initialization options.
    |
    */

  'libraries' => [
    'quill' => [
      'version' => '1.3.6',
      'css' => 'https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.snow.css',
      'js' => 'https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.js',
      'config' => [
        'theme' => 'snow',
        'placeholder' => "Type your email body here... You can drag variables.",
        'modules' => [
          'toolbar' => [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [['header' => 1], ['header' => 2]],
            [['list' => 'ordered'], ['list' => 'bullet']],
            [['script' => 'sub'], ['script' => 'super']],
            [['indent' => '-1'], ['indent' => '+1']],
            [['direction' => 'rtl']],
            [['size' => ['small', false, 'large', 'huge']]],
            [['header' => [1, 2, 3, 4, 5, 6, false]]],
            [['color' => []], ['background' => []]],
            [['font' => []]],
            [['align' => []]],
            ['clean'],
            ['link']
          ]
        ]
      ],
    ],
    'sweetalert' => [
      'version' => '11.x',
      'js' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11',
      'config' => [
        'success' => [
          'title' => 'Success!',
          'icon' => 'success',
          'confirmButtonColor' => '#31651e',
          'confirmButtonText' => 'Okay',
          'withConfirmButton' => true,
        ],
        'error' => [
          'title' => 'Error!',
          'icon' => 'error',
          'confirmButtonText' => 'Okay',
          'withConfirmButton' => true,
        ],
        'warning' => [
          'title' => 'Warning!',
          'icon' => 'warning',
          'confirmButtonText' => 'Okay',
          'withConfirmButton' => true,
        ],
        'info' => [
          'title' => 'Info!',
          'icon' => 'info',
          'confirmButtonText' => 'Okay',
          'withConfirmButton' => true,
        ]
      ]
    ],
    'fontawesome' => [
      'version' => '6.4.0',
      'css' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Color Scheme
    |--------------------------------------------------------------------------
    |
    | Define the color scheme for buttons, alerts, and other UI elements.
    |
    */

  'colors' => [
    'primary' => [
      'bootstrap' => 'btn-primary',
      'tailwind' => 'bg-blue-500 hover:bg-blue-600 text-white',
    ],
    'secondary' => [
      'bootstrap' => 'btn-secondary',
      'tailwind' => 'bg-gray-500 hover:bg-gray-600 text-white',
    ],
    'success' => [
      'bootstrap' => 'btn-success',
      'tailwind' => 'bg-green-500 hover:bg-green-600 text-white',
    ],
    'danger' => [
      'bootstrap' => 'btn-danger',
      'tailwind' => 'bg-red-500 hover:bg-red-600 text-white',
    ],
    'warning' => [
      'bootstrap' => 'btn-warning',
      'tailwind' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Form Styling
    |--------------------------------------------------------------------------
    |
    | Define form input and control styling.
    |
    */

  'forms' => [
    'input' => [
      'bootstrap' => 'form-control',
      'tailwind' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
    ],
    'textarea' => [
      'bootstrap' => 'form-control',
      'tailwind' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
    ],
    'select' => [
      'bootstrap' => 'form-select',
      'tailwind' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
    ],
    'checkbox' => [
      'bootstrap' => 'form-check-input',
      'tailwind' => 'mr-2',
    ],
    'file' => [
      'bootstrap' => 'form-control',
      'tailwind' => 'w-full px-3 py-2 border border-gray-300 rounded-md',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Button Styling
    |--------------------------------------------------------------------------
    |
    | Define button styling with sizes and states.
    |
    */

  'buttons' => [
    'sizes' => [
      'sm' => [
        'bootstrap' => 'btn-sm',
        'tailwind' => 'px-3 py-1 text-sm',
      ],
      'md' => [
        'bootstrap' => '',
        'tailwind' => 'px-4 py-2',
      ],
      'lg' => [
        'bootstrap' => 'btn-lg',
        'tailwind' => 'px-6 py-3 text-lg',
      ],
    ],
    'loading' => [
      'bootstrap' => 'wire:loading.attr="disabled"',
      'tailwind' => 'wire:loading.attr="disabled"',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Alert/Error Styling
    |--------------------------------------------------------------------------
    |
    | Define alert and error message styling.
    |
    */

  'alerts' => [
    'error' => [
      'bootstrap' => 'alert alert-danger',
      'tailwind' => 'bg-red-50 border border-red-200 rounded-md p-4',
    ],
    'success' => [
      'bootstrap' => 'alert alert-success',
      'tailwind' => 'bg-green-50 border border-green-200 rounded-md p-4',
    ],
    'warning' => [
      'bootstrap' => 'alert alert-warning',
      'tailwind' => 'bg-yellow-50 border border-yellow-200 rounded-md p-4',
    ],
    'info' => [
      'bootstrap' => 'alert alert-info',
      'tailwind' => 'bg-blue-50 border border-blue-200 rounded-md p-4',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Modal Styling
    |--------------------------------------------------------------------------
    |
    | Define modal styling and behavior.
    |
    */
  'modals' => [
    'overlay' => [
      'bootstrap' => 'modal-backdrop fade show',
      'tailwind' => 'fixed inset-0 z-[1040] bg-black bg-opacity-50 backdrop-blur-sm',
    ],
    'container' => [
      'bootstrap' => 'modal fade show',
      'tailwind' => 'fixed inset-0 z-[1050] flex items-center justify-center p-4',
    ],
    'dialog' => [
      'bootstrap' => 'modal-dialog modal-dialog-centered modal-lg',  // modal-lg instead of max-w-4xl equivalent
      'tailwind' => 'w-full max-w-4xl mx-auto',
    ],
    'content' => [
      'bootstrap' => 'modal-content bg-white border border-gray-300 rounded-lg shadow-lg',
      'tailwind' => 'bg-white border border-gray-300 rounded-lg shadow-lg',
    ],
    'header' => [
      'bootstrap' => 'modal-header ',
      'tailwind' => 'flex justify-between items-center p-4 border-b border-gray-200',
    ],
    'body' => [
      'bootstrap' => 'modal-body p-4 overflow-auto',  // Bootstrap has modal-body padding by default
      'tailwind' => 'p-4 max-h-[70vh] overflow-y-auto',
    ],
    'footer' => [
      'bootstrap' => 'modal-footer d-flex justify-content-end border-top',
      'tailwind' => 'flex justify-end p-4 border-t border-gray-200',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Table Styling
    |--------------------------------------------------------------------------
    |
    | Define table styling for recipients table.
    |
    */

  'tables' => [
    'container' => [
      'bootstrap' => 'table-responsive',
      'tailwind' => 'overflow-x-auto max-h-80 overflow-y-auto',
    ],
    'table' => [
      'bootstrap' => 'table table-bordered table-sm align-middle text-sm',
      'tailwind' => 'w-full border border-gray-300 text-sm',
    ],
    'thead' => [
      'bootstrap' => 'table-light',
      'tailwind' => 'bg-gray-50',
    ],
    'th' => [
      'bootstrap' => '',
      'tailwind' => 'border border-gray-300 px-3 py-2 text-left',
    ],
    'td' => [
      'bootstrap' => '',
      'tailwind' => 'border border-gray-300 px-3 py-2',
    ],
    'tr' => [
      'bootstrap' => '',
      'tailwind' => 'bg-white hover:bg-gray-50',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Icon Classes
    |--------------------------------------------------------------------------
    |
    | Define icon classes for different file types and actions.
    |
    */

  'icons' => [
    'file_types' => [
      'pdf' => [
        'bootstrap' => 'fas fa-file-pdf text-danger',
        'tailwind' => 'fas fa-file-pdf mr-3 text-red-500',
      ],
      'word' => [
        'bootstrap' => 'fas fa-file-word text-primary',
        'tailwind' => 'fas fa-file-word mr-3 text-blue-500',
      ],
      'image' => [
        'bootstrap' => 'fas fa-image text-primary',
        'tailwind' => 'fas fa-image mr-3 text-blue-500',
      ],
      'default' => [
        'bootstrap' => 'fas fa-file text-secondary',
        'tailwind' => 'fas fa-file mr-3 text-gray-500',
      ],
    ],
    'actions' => [
      'add' => 'fas fa-plus',
      'edit' => 'fas fa-edit',
      'delete' => 'fas fa-trash',
      'view' => 'fas fa-eye',
      'send' => 'fas fa-paper-plane',
      'clear' => 'fas fa-trash',
      'attachment' => 'fas fa-paperclip',
    ],
  ],
];
