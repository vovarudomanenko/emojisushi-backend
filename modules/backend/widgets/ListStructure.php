<?php namespace Backend\Widgets;

use Backend;
use BackendAuth;
use October\Rain\Database\Model;
use ApplicationException;
use ForbiddenException;

/**
 * ListStructure
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class ListStructure extends Lists
{
    /**
     * @var bool useStructure display parent/child relationships in the list.
     */
    public $useStructure = true;

    /**
     * @var bool showTree will display the tree structure
     */
    public $showTree = true;

    /**
     * @var bool treeExpanded will expand the tree nodes by default.
     */
    public $treeExpanded = true;

    /**
     * @var bool showReorder allows the user to reorder the records.
     */
    public $showReorder = true;

    /**
     * @var bool includeSortOrders specifies if "sort_orders" should be included in postback.
     */
    public $includeSortOrders = false;

    /**
     * @var bool includeReferencePool should be used when sorting within subset of records.
     * For example, sorting with pagination.
     */
    public $includeReferencePool = false;

    /**
     * @var int|null maxDepth defines the maximum levels allowed for reordering.
     */
    public $maxDepth = null;

    /**
     * @var bool dragRow allows dragging the entire row in addition to the reorder handle.
     */
    public $dragRow = true;

    /**
     * @var array permissions needed to modify the structure.
     */
    protected $permissions;

    /**
     * __construct the widget
     * @param \Backend\Classes\Controller $controller
     * @param array $configuration Proactive configuration definition.
     */
    public function __construct($controller, $configuration = [])
    {
        parent::__construct($controller, $configuration);

        // Extend view to include parent
        $parentViewPath = $this->guessViewPathFrom(Lists::class, '/partials');
        $this->addViewPath($parentViewPath, true);
    }

    /**
     * init the widget, called by the constructor and free from its parameters.
     */
    public function init()
    {
        // Defaults needed for reinit
        $this->useStructure = true;
        $this->showReorder = true;
        $this->showPagination = false;
        $this->showTree = true;

        $this->fillFromConfig([
            'maxDepth',
            'dragRow',
            'showTree',
            'showReorder',
            'treeExpanded',
            'includeSortOrders',
            'permissions'
        ]);

        parent::init();

        // Hide tree when sorting
        if ($this->isUserSorting()) {
            $this->disableStructure();
        }

        if ($this->showTree) {
            $this->validateTree();
        }
        else {
            $this->maxDepth = 1;
        }

        // Hide reorder without permission
        if (!$this->hasStructurePermission()) {
            $this->showReorder = false;
        }
    }

    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        $this->addJs('js/october.liststructure.js');
        $this->addJs('/modules/backend/widgets/lists/assets/js/october.list.js');
    }

    /**
     * prepareVars for display
     */
    public function prepareVars()
    {
        parent::prepareVars();

        // Alter tree status based on record content
        $this->showTree = $this->getIndentTreeStatus($this->records);

        $this->vars['useStructure'] = $this->useStructure;
        $this->vars['maxDepth'] = $this->maxDepth;
        $this->vars['dragRow'] = $this->dragRow;
        $this->vars['showTree'] = $this->showTree;
        $this->vars['showReorder'] = $this->showReorder;
        $this->vars['includeSortOrders'] = $this->useSortOrdering();
        $this->vars['treeLevel'] = 0;
        $this->vars['indentSize'] = $this->getIndentSize();
    }

    /**
     * disableStructure toggles the settings to completely disable the structure
     */
    protected function disableStructure()
    {
        $this->useStructure = false;
        $this->showReorder = false;
        $this->showPagination = true;
        $this->showTree = false;
    }

    /**
     * enableStructure reverts disableStructure
     */
    protected function enableStructure()
    {
        $this->sortColumn = null;
        $this->putSession('sort', null);
        $this->init();
    }

    /**
     * onSort AJAX handler for sorting the list.
     */
    public function onSort()
    {
        $column = post('sortColumn');
        if (!$column) {
            return;
        }

        // Spool up cache
        $this->getSortColumn();

        // Detect third click
        $isSameColumn = $column === $this->getSortColumn();
        $isFinalStep = $this->getSortStep() >= 2;
        $isSearchEmpty = empty($this->searchTerm);

        // Reset the list state and cache
        if ($isSameColumn && $isFinalStep && $isSearchEmpty) {
            $this->enableStructure();
            return $this->onRefresh();
        }

        // Disable structure when sorting
        $this->disableStructure();

        return parent::onSort();
    }

    /**
     * onShowStructure
     */
    public function onShowStructure()
    {
        $this->enableStructure();
        return $this->onRefresh();
    }

    /**
     * useSorting
     */
    protected function useSorting(): bool
    {
        return !$this->useStructure;
    }

    /**
     * setSearchTerm will disable the structure if a value is supplied.
     */
    public function setSearchTerm($term, $resetState = false)
    {
        if (!empty($term)) {
            $this->disableStructure();
        }
        elseif ($resetState) {
            $this->enableStructure();
        }

        parent::setSearchTerm($term, $resetState);
    }

    /**
     * getRecords returns all the records from the supplied model, after filtering
     * @return Collection
     */
    protected function getRecords()
    {
        if (!$this->useStructure || !$this->showTree) {
            return parent::getRecords();
        }

        // Find records
        $records = $this->prepareQuery()->getNested();

        // Extensibility from parent
        if ($event = $this->fireSystemEvent('backend.list.extendRecords', [&$records])) {
            $records = $event;
        }

        return $this->records = $records;
    }

    /**
     * getTotalColumns calculates the total columns used in the list, including checkboxes
     * and other additions.
     */
    protected function getTotalColumns()
    {
        $total = parent::getTotalColumns();

        if ($this->showReorder) {
            $total++;
        }

        return $total;
    }

    /**
     * getIndentSize returns the size increment of indentation
     */
    protected function getIndentSize(): int
    {
        return 18;
    }

    /**
     * getIndentStartSize is used to pad each row based on its tree level
     */
    protected function getIndentStartSize(int $treeLevel): int
    {
        return ($treeLevel * $this->getIndentSize()) +
            ($this->showTree ? 15 : 0) +
            ($this->showReorder ? 0 : 15);
    }

    /**
     * getIndentTreeStatus checks if the collapse UI should be shown
     * based on if any records have children.
     */
    protected function getIndentTreeStatus($records): bool
    {
        if (!$this->showTree) {
            return false;
        }

        foreach ($records as $record) {
            if ($record->getChildCount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * validateTree validates the model and settings if useStructure is used
     */
    public function validateTree()
    {
        if (!$this->model->isClassInstanceOf(\October\Contracts\Database\TreeInterface::class)) {
            $modelClass = get_class($this->model);
            throw new ApplicationException(
                "To display list as a tree, the model {$modelClass} must implement methods found in October\Contracts\Database\TreeInterface, or set showTree to false"
            );
        }
    }

    /**
     * useSortOrdering
     */
    public function useSortOrdering(): bool
    {
        return $this->includeSortOrders || $this->model->isClassInstanceOf(\October\Contracts\Database\SortableInterface::class);
    }

    /**
     * onReorder
     */
    public function onReorder()
    {
        if (!$this->hasStructurePermission()) {
            throw new ForbiddenException;
        }

        $itemId = post('record_id');
        if (!$itemId) {
            return;
        }

        $item = $this->model->newQueryWithoutScopes()->find($itemId);
        if (!$item) {
            return;
        }

        if ($this->fireSystemEvent('backend.list.beforeReorderStructure', [$item], true) === false) {
            return $this->onRefresh();
        }

        if ($this->model->isClassInstanceOf(\October\Contracts\Database\NestedSetInterface::class)) {
            $this->reorderForNestedTree($item);
        }
        else {
            if ($this->model->hasRelation('parent')) {
                $this->reorderForSimpleTree($item);
            }

            if ($this->model->isClassInstanceOf(\October\Contracts\Database\SortableInterface::class)) {
                $this->reorderForSortable($item);
            }
        }

        $this->fireSystemEvent('backend.list.reorderStructure', [$item]);

        return $this->onRefresh();
    }

    /**
     * reorderForSimpleTree
     */
    protected function reorderForSimpleTree($item)
    {
        $item->parent = post('parent_id');
        $item->save(['force' => true]);
    }

    /**
     * reorderForSortable
     */
    protected function reorderForSortable($item)
    {
        $item->setSortableOrder(post('sort_orders'), $this->includeReferencePool ? null : true);
    }

    /**
     * reorderForNestedTree
     */
    protected function reorderForNestedTree($item)
    {
        if ($prevId = post('previous_id')) {
            $item->moveAfter($prevId);
        }
        elseif ($nextId = post('next_id')) {
            $item->moveBefore($nextId);
        }
        elseif ($parentId = post('parent_id')) {
            $item->makeChildOf($parentId);
        }
    }

    /**
     * onToggleTreeNode sets a node (model) to an expanded or collapsed state, stored in the
     * session, then renders the list again.
     */
    public function onToggleTreeNode()
    {
        $this->putSession('tree_node_status_' . post('node_id'), post('status') ? 0 : 1);

        return $this->onRefresh();
    }

    /**
     * isTreeNodeExpanded checks if a node (model) is expanded in the session.
     * @param  Model $node
     * @return bool
     */
    public function isTreeNodeExpanded($node)
    {
        return $this->getSession('tree_node_status_' . $node->getKey(), $this->treeExpanded);
    }

    /**
     * hasStructurePermission checks if the user has permissions to modify the structure.
     */
    protected function hasStructurePermission(): bool
    {
        if (!$this->permissions) {
            return true;
        }

        return BackendAuth::userHasAccess($this->permissions, false);
    }
}
