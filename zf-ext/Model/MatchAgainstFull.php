<?php 
namespace Zf\Ext\Model;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class MatchAgainstFull extends FunctionNode
{
    /**
     *
     * @var array|null
     */
    protected ?array $pathExp = null;

    /**
     *
     * @var string|null
     */
    protected ?string $against = null;

    /**
     *
     * @var boolean
     */
    protected bool $booleanMode = false;

    /**
     *
     * @var boolean
     */
    protected bool $queryExpansion = false;

    /**
     *
     * @var boolean
     */
    protected bool $queryNatural = false;
    
    /**
     *
     * @param Parser $parser
     * @return void
     */
    /**
     * construct
     *
     * @param \Doctrine\ORM\Query\Parser $parser
     * @return void
     */
    public function parse(Parser $parser)
    {
        dd($parser);
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->pathExp = [];
        $this->pathExp[] = $parser->StateFieldPathExpression();

        $lexer = $parser->getLexer();
        while ($lexer->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->pathExp[] = $parser->StateFieldPathExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

        if (strtolower($lexer->lookahead['value']) !== 'against') {
            $parser->syntaxError('against');
        }

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->against = $parser->StringPrimary();

        if (strtolower($lexer->lookahead['value']) === 'boolean') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->booleanMode = true;
        }

        if (strtolower($lexer->lookahead['value']) === 'expand') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->queryExpansion = true;
        }
		if (strtolower($lexer->lookahead['value']) === 'natural') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->queryNatural = true;
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    public function getSql(SqlWalker $walker): string
    {
        $fields = [];

        foreach ($this->pathExp as $pathExp) {
            $fields[] = $pathExp->dispatch($walker);
        }

        $against = $walker->walkStringPrimary($this->against)
        . ($this->booleanMode ? ' IN BOOLEAN MODE' : '')
        . ($this->queryExpansion ? ' WITH QUERY EXPANSION' : '')
		. ($this->queryNatural ? ' IN NATURAL LANGUAGE MODE' : '')
		;

        return sprintf('MATCH (%s) AGAINST (%s)', implode(', ', $fields), $against);
    }

}