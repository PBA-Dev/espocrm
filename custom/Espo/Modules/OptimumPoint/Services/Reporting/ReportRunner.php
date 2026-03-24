<?php

namespace Espo\Modules\OptimumPoint\Services\Reporting;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Selection;
use Espo\ORM\Query\SelectBuilder;

class ReportRunner
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function run(string $reportId): array
    {
        $report = $this->entityManager->getRepository('OpReport')->getById($reportId);

        if (!$report) {
            throw new NotFound('Report not found.');
        }

        $entityType = $report->get('targetEntityType');
        $reportType = $report->get('reportType') ?? 'List';
        $whereClause = $this->buildWhereClause($report->get('filterDefinition') ?? []);

        return match ($reportType) {
            'Count' => $this->runCount($report, $entityType, $whereClause),
            'GroupedCount' => $this->runGroupedCount($report, $entityType, $whereClause),
            default => $this->runList($report, $entityType, $whereClause),
        };
    }

    private function runList(Entity $report, string $entityType, array $whereClause): array
    {
        $builder = $this->entityManager
            ->getRepository($entityType)
            ->where($whereClause);

        $sortBy = $report->get('sortBy');

        if ($sortBy) {
            $builder->order($sortBy, $report->get('sortDirection') ?? 'desc');
        }

        $maxSize = max(1, (int) ($report->get('maxSize') ?? 50));

        $collection = $builder
            ->limit($maxSize)
            ->find();

        $displayFieldList = $report->get('displayFieldList') ?? [];

        if (!is_array($displayFieldList) || $displayFieldList === []) {
            $displayFieldList = ['id', 'name'];
        }

        $rowList = [];

        foreach ($collection as $entity) {
            $row = ['id' => $entity->getId()];

            foreach ($displayFieldList as $field) {
                if (!is_string($field) || $field === 'id') {
                    continue;
                }

                $row[$field] = $entity->get($field);
            }

            $rowList[] = $row;
        }

        return [
            'reportId' => $report->getId(),
            'reportType' => 'List',
            'targetEntityType' => $entityType,
            'visualizationType' => $report->get('visualizationType') ?? 'Table',
            'rowList' => $rowList,
            'count' => count($rowList),
        ];
    }

    private function runCount(Entity $report, string $entityType, array $whereClause): array
    {
        $query = SelectBuilder::create()
            ->from($entityType)
            ->select([
                Selection::create(Expression::count(Expression::column('id')), 'count'),
            ])
            ->where($whereClause)
            ->build();

        $row = $this->entityManager
            ->getQueryExecutor()
            ->execute($query)
            ->fetch() ?: ['count' => 0];

        return [
            'reportId' => $report->getId(),
            'reportType' => 'Count',
            'targetEntityType' => $entityType,
            'visualizationType' => $report->get('visualizationType') ?? 'Metric',
            'count' => (int) ($row['count'] ?? 0),
        ];
    }

    private function runGroupedCount(Entity $report, string $entityType, array $whereClause): array
    {
        $groupByField = $report->get('groupByField');

        if (!$groupByField) {
            throw new BadRequest('No groupByField for grouped report.');
        }

        $query = SelectBuilder::create()
            ->from($entityType)
            ->select([
                Selection::create(Expression::column($groupByField), 'groupKey'),
                Selection::create(Expression::count(Expression::column('id')), 'count'),
            ])
            ->where($whereClause)
            ->group($groupByField)
            ->build();

        $rowList = $this->entityManager
            ->getQueryExecutor()
            ->execute($query)
            ->fetchAll() ?: [];

        return [
            'reportId' => $report->getId(),
            'reportType' => 'GroupedCount',
            'targetEntityType' => $entityType,
            'visualizationType' => $report->get('visualizationType') ?? 'BarChart',
            'groupByField' => $groupByField,
            'rowList' => array_map(
                fn (array $row) => [
                    'groupKey' => $row['groupKey'] ?? null,
                    'count' => (int) ($row['count'] ?? 0),
                ],
                $rowList
            ),
        ];
    }

    private function buildWhereClause(array $filterDefinition): array
    {
        $whereClause = [];

        foreach ($filterDefinition as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? 'equals';
            $value = $filter['value'] ?? null;

            if (!$field || !is_string($field)) {
                continue;
            }

            $whereClause[] = match ($operator) {
                'notEquals' => [$field . '!=' => $value],
                'in' => [$field => is_array($value) ? $value : [$value]],
                'notIn' => [$field . '!=' => is_array($value) ? $value : [$value]],
                'greaterThan' => [$field . '>' => $value],
                'lessThan' => [$field . '<' => $value],
                'greaterOrEquals' => [$field . '>=' => $value],
                'lessOrEquals' => [$field . '<=' => $value],
                default => [$field => $value],
            };
        }

        return $whereClause;
    }
}
