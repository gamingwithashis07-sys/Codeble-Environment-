<?php

declare(strict_types=1);

namespace LoveGem\View\Compilers;

class BladeCompiler
{
    protected array $customDirectives = [];

    protected array $compilers = [
        'Comments',
        'Extensions',
        'Statements',
        'Keywords',
    ];

    public function compile(string $template): string
    {
        $template = $this->compileComments($template);
        $template = $this->compileExtends($template);
        $template = $this->compileIncludes($template);
        $template = $this->compileStatements($template);
        $template = $this->compileEchos($template);
        $template = $this->compileRawEchos($template);
        $template = $this->compileYields($template);
        $template = $this->compileSections($template);
        $template = $this->compileIncludes($template);

        return $template;
    }

    protected function compileComments(string $template): string
    {
        return preg_replace('/\{\{--(.+?)--\}\}/s', '<?php /* $1 */ ?>', $template);
    }

    protected function compileExtends(string $template): string
    {
        $pattern = '/@extends\s*\(\s*[\'"](.+?)[\'"]\s*\)/s';

        return preg_replace($pattern, '<?php echo $__env->extends($1, get_defined_vars()); ?>', $template);
    }

    protected function compileIncludes(string $template): string
    {
        $pattern = '/@include\s*\(\s*[\'"](.+?)[\'"]\s*(?:,\s*(.+?))?\)/s';

        return preg_replace($pattern, '<?php echo $__env->make($1, $2 ?? [], get_defined_vars())->render(); ?>', $template);
    }

    protected function compileStatements(string $template): string
    {
        $pattern = '/\B@(@?\w+(?:::\w+)?)\s*\( ( args ) \)/sx';

        if (preg_match_all($pattern, $template, $matches)) {
            foreach ($matches[0] as $index => $statement) {
                $template = str_replace(
                    $matches[0][$index],
                    $this->compileStatement($matches[1][$index], $matches[2][$index] ?? ''),
                    $template
                );
            }
        }

        $template = $this->compileForelse($template);
        $template = $this->compileForelseExtended($template);
        $template = $this->compileSwitch($template);
        $template = $this->compileCases($template);
        $template = $this->compileEndSwitch($template);

        return $template;
    }

    protected function compileStatement(string $statement, string $args): string
    {
        $args = trim($args);

        $patternMap = [
            'if' => "<?php if(%s): ?>",
            'elseif' => "<?php elseif(%s): ?>",
            'else' => "<?php else: ?>",
            'endif' => "<?php endif; ?>",
            'for' => "<?php for(%s): ?>",
            'endfor' => "<?php endfor; ?>",
            'foreach' => "<?php foreach(%s): ?>",
            'endforeach' => "<?php endforeach; ?>",
            'while' => "<?php while(%s): ?>",
            'endwhile' => "<?php endwhile; ?>",
            'unless' => "<?php if(!(%s)): ?>",
            'endunless' => "<?php endif; ?>",
            'continue' => "<?php continue; ?>",
            'break' => "<?php break; ?>",
            'php' => "<?php %s; ?>",
            'verbatim' => "<?php echo e(%s); ?>",
            'verbatimEof' => "<?php echo e(%s); ?>",
            'verbatimEnd' => "<?php echo e(%s); ?>",
        ];

        if (isset($patternMap[$statement])) {
            return sprintf($patternMap[$statement], $args);
        }

        if (str_contains($statement, '@')) {
            $statement = substr($statement, 1);
        }

        if (isset($this->customDirectives[$statement])) {
            $result = call_user_func($this->customDirectives[$statement], $args);
            return "<?php {$result} ?>";
        }

        return "<?php echo e({$statement}({$args})); ?>";
    }

    protected function compileForelse(string $template): string
    {
        $pattern = '/@forelse\s*\(\s*\$(\w+)\s+as\s+\$(\w+)\s*\)/s';

        return preg_replace($pattern, '<?php foreach($$1 as $$2): ?>', $template);
    }

    protected function compileForelseExtended(string $template): string
    {
        $pattern = '/@empty\s*(?:\(\s*\))?\s*->\s*([\s\S]*?)\s*@endforelse/s';

        return preg_replace($pattern, '<?php endforeach; if(count($$1) === 0): ?> $1 <?php endif; ?>', $template);
    }

    protected function compileSwitch(string $template): string
    {
        $pattern = '/@switch\s*\(\s*(.+?)\s*\)/s';

        return preg_replace($pattern, '<?php switch($1): ?>', $template);
    }

    protected function compileCases(string $template): string
    {
        $pattern = '/@case\s*\(\s*(.+?)\s*\)/s';

        return preg_replace($pattern, '<?php case $1: ?>', $template);
    }

    protected function compileEndSwitch(string $template): string
    {
        $pattern = '/@endswitch/s';

        return preg_replace($pattern, '<?php endswitch; ?>', $template);
    }

    protected function compileEchos(string $template): string
    {
        $template = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?php echo e($1); ?>', $template);

        $template = preg_replace('/\{\{\{\s*(.+?)\s*\}\}\}/s', '<?php echo $1; ?>', $template);

        return $template;
    }

    protected function compileRawEchos(string $template): string
    {
        return preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?php echo $1; ?>', $template);
    }

    protected function compileYields(string $template): string
    {
        return preg_replace('/@yield\s*\(\s*[\'"](.+?)[\'"]\s*(?:,\s*(.+?))?\)/s', '<?php echo $__env->yield($1, $2); ?>', $template);
    }

    protected function compileSections(string $template): string
    {
        $template = preg_replace('/@section\s*\(\s*[\'"](.+?)[\'"]\s*\)/s', '<?php $__env->startSection($1); ?>', $template);
        $template = preg_replace('/@section\s*\(\s*[\'"](.+?)[\'"]\s*,\s*(.+?)\s*\)/s', '<?php $__env->startSection($1); ?> $2 <?php $__env->stopSection(); ?>', $template);
        $template = preg_replace('/@show\b/s', '<?php echo $__env->yieldSection(); ?>', $template);
        $template = preg_replace('/@append\b/s', '<?php $__env->appendSection(); ?>', $template);
        $template = preg_replace('/@endsection\b/s', '<?php $__env->stopSection(); ?>', $template);

        return $template;
    }

    public function directive(string $name, callable $callback): void
    {
        $this->customDirectives[$name] = $callback;
    }

    public function getCustomDirectives(): array
    {
        return $this->customDirectives;
    }

    protected function parseStatements(string $value): string
    {
        $pattern = '/\B@(@?\w+(?:::\w+)?)\s*\(\s*(.*)?\)/s';

        return preg_replace_callback($pattern, function ($match) {
            $statement = $match[1];
            $args = $match[2] ?? '';

            return $this->compileStatement($statement, $args);
        }, $value);
    }
}
