<?php
namespace Zf\Ext\Model;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;

class ZFDtPaginator extends \Laminas\Paginator\Paginator
{
    /**
     * @var bool
     */
    protected $_fetchJoinCollection = true;

    /**
     * @var	string
     */
    const FETCH_JOIN_COLLECTION = 'fetchJoinCollection';

    /**
     * Constructor
     * 
     * @param Query $query            
     * @param bool $_fetchJoinCollection            
     */
    public function __construct(Query $query)
    {
        foreach ($query->getParameters() as $index => $parameter) {
            switch ($parameter->getName()) {
                case self::FETCH_JOIN_COLLECTION:
                    $this->_fetchJoinCollection = $parameter->getValue();
                    $query->getParameters()->offsetUnset($index);
                    break;
                default:
                    break;
            }
        }
        parent::__construct(new DoctrinePaginator(
            new Paginator($query, $this->_fetchJoinCollection)
        ));
    }
    
    /**
     * Brings the page number in range of the paginator.
     *
     * @param  int $pageNumber
     * @return int
     */
    public function normalizePageNumber($pageNumber)
    {
        $pageNumber = (int) $pageNumber;
    
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }
    
        $pageCount = $this->count();
    
        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount+1;
        }
    
        return $pageNumber;
    }
}
