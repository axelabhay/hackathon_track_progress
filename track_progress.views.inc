<?php

/**
 * @file
 * Provide views data for track_progress.module.
 *
 * @ingroup views_module_handlers
 */

/**
 * Implements hook_views_data().
 */
function track_progress_views_data() {
  $data = [];

  // Activity table(base) definition.
  $data['track_progress_activity'] = [
    'table' => [
      'group' => t('Track Progress- Activity'),
      'base' => [
        'field' => 'aid',
        'title' => t('Track Progress'),
        'help' => t('Activity table contains example content and can be related to nodes.'),
        'weight' => -10,
      ],
    ],
  ];

  // Activity table field mappings.
  $data['track_progress_activity'] += [
    // While creating view this comes as default field. //@todo
    // @todo Need AID as default value.
    'aid' => [
      'title' => t('Activity ID'),
      'help' => t('The track_progress page node.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'argument' => ['id' => 'numeric'],
    ],
    // @todo link to track_progress.activity_interact and others too if required.
    'title' => [
      'title' => t('Title'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'standard',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'string'],
      'argument' => ['id' => 'string'],
    ],
    'active' => [
      'title' => t('Is Active?'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'boolean',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => [
        // ID of filter handler plugin to use.
        'id' => 'boolean',
        // Override the generic field title, so that the filter uses a different
        // label in the UI.
        'label' => t('Published'),
        // Override the default BooleanOperator filter handler's 'type' setting,
        // to display this as a "Yes/No" filter instead of a "True/False" filter.
        'type' => 'yes-no',
        // Override the default Boolean filter handler's 'use_equal' setting, to
        // make the query use 'boolean_field = 1' instead of 'boolean_field <> 0'.
        'use_equal' => TRUE,
      ],
      'argument' => ['id' => 'standard'],
    ],
    'weighted_progress' => [
      'title' => t('Is Weighted Progress Allowed?'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'boolean',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => [
        // ID of filter handler plugin to use.
        'id' => 'boolean',
        // Override the generic field title, so that the filter uses a different
        // label in the UI.
        'label' => t('Published'),
        // Override the default BooleanOperator filter handler's 'type' setting,
        // to display this as a "Yes/No" filter instead of a "True/False" filter.
        'type' => 'yes-no',
        // Override the default Boolean filter handler's 'use_equal' setting, to
        // make the query use 'boolean_field = 1' instead of 'boolean_field <> 0'.
        'use_equal' => TRUE,
      ],
      'argument' => ['id' => 'standard'],
    ],
    'archive_on' => [
      'title' => t('Archive On'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'date',
      ],
      'sort' => ['id' => 'date'],
      'filter' => ['id' => 'date'],
      'argument' => ['id' => 'date'],
    ],
    'user' => [
      'title' => t('Author'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'argument' => ['id' => 'numeric'],
      // Add relationship so that users_field_data defined views element could be used.
      'relationship' => [
        'title' => t('User'),
        'help' => t('Track progress user relationship.'),
        'base' => 'users_field_data',
        'base field' => 'uid',
        'id' => 'standard',
        'label' => 'User (Track Progress)'
      ],
    ],
    'weight' => [
      'title' => t('Weight'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'argument' => ['id' => 'numeric'],
    ],
  ];


  // @todo make view pages look n feel like admin.
    // web/core/modules/views/src/Plugin/views/field/EntityOperations.php
    // Activity custom fields.
    $data['track_progress_activity']['activity_operations'] = [
      'title' => t('Activity Operations'),
      'help' => t('Provides links to perform activity related operations.'),
      'field' => [
        'id' => 'track_progress_operation_links',
      ],
    ];
    $data['track_progress_activity']['activity_progress'] = [
      'title' => t('Activity Progress links'),
      'help' => t('Provides activity progress made so far.'),
      'field' => [
        'id' => 'track_progress_activity_progress',
      ],
    ];
    $data['track_progress_activity']['tabledrag'] = [
      'title' => t('Add Tabledrag'),
      'help' => t('Add a form element that lets you run operations on multiple nodes.'),
      'field' => [
        'id' => 'track_progress_weight_tabledrag',
      ],
    ];
    $data['track_progress_activity']['bulkaction'] = [
      'title' => t('Add BulkAction)'),
      'help' => t('Add a form element that lets you run operations on multiple nodes.'),
      'field' => [
        'id' => 'track_progress_bulk_action',
      ],
    ];

  // Task table definition.
  $data['track_progress_task'] = [
    'table' => [
      'group' => t('Track Progress- Task'),
      'join' => [
        'track_progress_activity' => [
          'left_field' => 'aid',
          'field' => 'activity',
        ],
      ]
    ]
  ];

  // Task table field mappings.
  $data['track_progress_task'] += [
    'tid' => [
      'title' => t('Task ID'),
      'help' => t('The track_progress page node.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'argument' => ['id' => 'numeric'],
    ],
    'title' => [
      'title' => t('Task Title'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'standard',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'string'],
      'argument' => ['id' => 'string'],
    ],
    'progress' => [
      'title' => t('Task progress'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'weightage' => [
      'title' => t('weightage weight'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'argument' => ['id' => 'numeric'],
    ],
    // @todo this field requires taskID field i guess.
    'weight' => [
      'title' => t('Task Weight'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'argument' => ['id' => 'numeric'],
    ],
  ];

  // Task custom fields.
  $data['track_progress_task'] += [
    'task_operations' => [
      'field' => [
        'title' => t('Task Operations links'),
        'help' => t('Provides links to perform task related operations.'),
        'id' => 'track_progress_operation_links',
      ],
    ],
  ];

  // Joining track_progress_category to track_progress_task via track_progress_activity(base).
  // @todo Check query if has be somewhat lik $data['track_progress_activity']['join']['track_progress_task']
  $data['track_progress_category'] = [
    'table' => [
      'group' => t('Track Progress- Category'),
      'join' => [
        'track_progress_activity' => [
          'left_table' => 'track_progress_task',
          'left_field' => 'category',
          'field' => 'cid',
        ],
      ]
    ]
  ];

  // Category table field mappings.
  $data['track_progress_category'] += [
    'cid' => [
      'title' => t('Category ID'),
      'help' => t('The track_progress page node.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => ['id' => 'standard'],
      'filter' => ['id' => 'numeric'],
      'argument' => ['id' => 'numeric'],
    ],
    'title' => [
      'title' => t('Category Title'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'standard',
      ],
    ],
    'color' => [
      'title' => t('Category Color'),
      'help' => t('The track_progress the node is in.'),
      'field' => [
        'id' => 'standard',
      ],
    ],
  ];


  // Category custom fields.
  $data['track_progress_category'] += [
    'category_operations' => [
      'title' => t('Category Operations links'),
      'help' => t('Provides links to perform category related operations.'),
      'field' => [
        'id' => 'track_progress_operation_links',
      ],
    ],
  ];



  $data['track_progress_task']['title'] = [
    'title' => t('Task Title'),
    'help' => t('The track_progress the node is in.'),
    'field' => [
      // ID of field handler plugin to use.
      'id' => 'standard',
    ],
  ];
  return $data;
}
