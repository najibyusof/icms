<?php

namespace Modules\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Workflow\Models\WorkflowApproval;
use Modules\Workflow\Models\WorkflowInstance;

class WorkflowDecisionRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public WorkflowInstance $workflow, public WorkflowApproval $approval)
    {
    }
}
