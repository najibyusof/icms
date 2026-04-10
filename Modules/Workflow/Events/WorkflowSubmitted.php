<?php

namespace Modules\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Workflow\Models\WorkflowInstance;

class WorkflowSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public WorkflowInstance $workflow)
    {
    }
}
