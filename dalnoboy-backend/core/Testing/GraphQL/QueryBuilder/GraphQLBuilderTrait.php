<?php

namespace Core\Testing\GraphQL\QueryBuilder;

trait GraphQLBuilderTrait
{
    protected function implodeRecursive(string $glue, array $parameters): string
    {
        $output = '';

        foreach ($parameters as $key => $parameter) {
            if (is_array($parameter)) {
                $output .= $key . ' {' . $this->implodeRecursive($glue, $parameter) . $glue . '} ';
            } else {
                $output .= $parameter . $glue;
            }
        }

        return substr($output, 0, 0 - strlen($glue));
    }
}
