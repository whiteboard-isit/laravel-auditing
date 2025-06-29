<?php

namespace OwenIt\Auditing\Drivers;

use OwenIt\Auditing\Contracts\Audit;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\AuditDriver;

class Database implements AuditDriver
{
    /**
     * {@inheritdoc}
     */
    public function audit(Auditable $model): ?Audit
    {
        return call_user_func([get_class($model->audits()->getModel()), 'create'], $model->toAudit());
    }

    /**
     * {@inheritdoc}
     */
    public function prune(Auditable $model): bool
    {
        if (($threshold = $model->getAuditThreshold()) > 0) {
            $recentIds = $model->audits()
                ->orderBy('created_at', 'desc') // MongoDB usually uses _id as primary key
                ->limit($threshold)
                ->pluck('_id');
            return $model->audits()
                ->whereNotIn('_id', $recentIds)
                ->delete() > 0;
        }

        return false;
    }
}
