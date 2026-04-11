<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Difficulty Distribution Targets
    |--------------------------------------------------------------------------
    | Bloom's taxonomy groups with target percentage of total marks.
    | Bloom levels 1-2 = Lower Order (LOTS)
    | Bloom levels 3-4 = Middle Order (MOTS)
    | Bloom levels 5-6 = Higher Order (HOTS)
    |
    | These defaults can be overridden per-JSU via the difficulty_config column.
    */
    'difficulty_distribution' => [
        'lower'  => ['bloom_levels' => [1, 2], 'target_pct' => 30],  // Remember, Understand
        'middle' => ['bloom_levels' => [3, 4], 'target_pct' => 50],  // Apply, Analyse
        'higher' => ['bloom_levels' => [5, 6], 'target_pct' => 20],  // Evaluate, Create
    ],

    /*
    |--------------------------------------------------------------------------
    | Bloom's Level Labels
    |--------------------------------------------------------------------------
    */
    'bloom_levels' => [
        1 => 'Remembering',
        2 => 'Understanding',
        3 => 'Applying',
        4 => 'Analysing',
        5 => 'Evaluating',
        6 => 'Creating',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Examination Types
    |--------------------------------------------------------------------------
    */
    'exam_types' => ['midterm', 'final', 'quiz', 'test', 'assignment'],

    /*
    |--------------------------------------------------------------------------
    | Distribution Tolerance (percentage points)
    |--------------------------------------------------------------------------
    | How much a JSU may deviate from each target before being flagged.
    */
    'distribution_tolerance' => 5,

    /*
    |--------------------------------------------------------------------------
    | Default Workflow Template Version for JSU
    |--------------------------------------------------------------------------
    */
    'workflow_default_version' => 1,

];
