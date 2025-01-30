<?php

namespace App\Entity\Project;

enum BudgetItemType: string
{
    case Infrastructure = 'infrastructure';
    case Material = 'material';
    case Task = 'task';
}
