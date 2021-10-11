<?php

declare(strict_types=1);

namespace Termage\Base;

use Atomastic\Arrays\Arrays;
use Atomastic\Strings\Strings;
use BadMethodCallException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Termage\Parsers\Shortcodes;
use Termage\Themes\DefaultTheme;

use function arrays;
use function intval;
use function sprintf;
use function strings;
use function substr;

abstract class Element
{
    /**
     * The implementation of the output.
     *
     * @access private
     */
    private OutputInterface $output;

    /**
     * The instance of Terminal class.
     *
     * @access private
     */
    private Terminal $terminal;

    /**
     * Element properties.
     *
     * @access private
     */
    private Arrays $properties;

    /**
     * Element value.
     *
     * @access private
     */
    private Strings $value;

    /**
     * The instance of Theme class.
     *
     * @access private
     */
    private Theme $theme;

    /**
     * Create a new Element instance.
     *
     * @param OutputInterface $output     Output interface.
     * @param InputInterface  $input      Input interface.
     * @param Theme           $theme      Instance of the Theme class.
     * @param string          $value      Element value.
     * @param array           $properties Element properties.
     *
     * @return Element Returns element.
     *
     * @access public
     */
    final public function __construct(
        ?OutputInterface $output = null,
        ?Theme $theme = null,
        ?Shortcodes $shortcodes = null,
        string $value = '',
        array $properties = []
    ) {
        $this->output     = $output ??= new ConsoleOutput();
        $this->theme      = $theme ??= new DefaultTheme();
        $this->terminal   = new Terminal();
        $this->shortcodes = $shortcodes ??= new Shortcodes($this->theme);
        $this->value      = strings($value);
        $this->properties = arrays($properties);
    }

    /**
     * Get element value.
     *
     * @return Strings Returns element value.
     *
     * @access public
     */
    public function getValue(): Strings
    {
        return $this->value;
    }

    /**
     * Get element output.
     *
     * @return OutputInterface Returns element output.
     *
     * @access public
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Get element theme.
     *
     * @return Theme Returns element theme.
     *
     * @access public
     */
    public function getTheme(): Theme
    {
        return $this->theme;
    }

    /**
     * Get element properties.
     *
     * @return Arrays Returns element properties.
     *
     * @access public
     */
    public function getProperties(): Arrays
    {
        return $this->properties;
    }

    /**
     * Set element value.
     *
     * @param string $value Element value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function value(string $value = ''): self
    {
        $this->value = strings($value);

        return $this;
    }

    /**
     * Set element properties.
     *
     * @param string $properties Element properties.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function properties(array $properties = []): self
    {
        $this->properties = arrays($properties);

        return $this;
    }

    /**
     * Set element output.
     *
     * @param OutputInterface $output Element output interface.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function output(OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Set element color.
     *
     * @param string $color Element color.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function color(string $color): self
    {
        $this->properties->set('color', $color);

        return $this;
    }

    /**
     * Set element background color.
     *
     * @param string $color Element background color.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function bg(string $color): self
    {
        $this->properties->set('bg', $color);

        return $this;
    }

    /**
     * Set element bold property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function bold(): self
    {
        $this->value = strings('[b]' . $this->value . '[/b]');

        return $this;
    }

    /**
     * Set element italic property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function italic(): self
    {
        $this->value = strings('[i]' . $this->value . '[/i]');

        return $this;
    }

    /**
     * Set element strikethrough property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function strikethrough(): self
    {
        $this->value = strings('[s]' . $this->value . '[/s]');

        return $this;
    }

    /**
     * Set element dim property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function dim(): self
    {
        $this->value = strings('[dim]' . $this->value . '[/dim]');

        return $this;
    }

    /**
     * Set element underline property, alias to underscore.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function underline(): self
    {
        $this->value = strings('[u]' . $this->value . '[/u]');

        return $this;
    }

    /**
     * Set element underscore property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function underscore(): self
    {
        $this->value = strings('[u]' . $this->value . '[/u]');

        return $this;
    }

    /**
     * Set element blink property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function blink(): self
    {
        $this->value = strings('[blink]' . $this->value . '[/blink]');

        return $this;
    }

    /**
     * Set element reverse property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function reverse(): self
    {
        $this->value = strings('[reverse]' . $this->value . '[/reverse]');

        return $this;
    }

    /**
     * Set element invisible property.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function invisible(): self
    {
        $this->value = strings('[invisible]' . $this->value . '[/invisible]');

        return $this;
    }

    /**
     * Set element margin x property.
     *
     * @param int $value Margin x.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function mx(int $value): self
    {
        $themeMarginGlobal = $this->theme->variables()->get('margin.global', 1);
        $themeMarginLeft   = $this->theme->variables()->get('margin.left', 1);
        $themeMarginRight  = $this->theme->variables()->get('margin.right', 1);

        $this->properties->set('margin.left', intval($value / 2 * $themeMarginLeft * $themeMarginGlobal));
        $this->properties->set('margin.right', intval($value / 2 * $themeMarginRight * $themeMarginGlobal));

        return $this;
    }

    /**
     * Set element margin left property.
     *
     * @param int $value Margin left.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function ml(int $value): self
    {
        $themeMarginGlobal = $this->theme->variables()->get('margin.global', 1);
        $themeMarginLeft   = $this->theme->variables()->get('margin.left', 1);

        $this->properties->set('margin.left', intval($value * $themeMarginLeft * $themeMarginGlobal));

        return $this;
    }

    /**
     * Set element margin right property.
     *
     * @param int $value Margin right.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function mr(int $value): self
    {
        $themeMarginGlobal = $this->theme->variables()->get('margin.global', 1);
        $themeMarginRight  = $this->theme->variables()->get('margin.right', 1);

        $this->properties->set('margin.right', intval($value * $themeMarginRight * $themeMarginGlobal));

        return $this;
    }

    /**
     * Set element padding x property.
     *
     * @param int $value Padding x.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function px(int $value): self
    {
        $themePaddingGlobal = $this->theme->variables()->get('padding.global', 1);
        $themePaddingLeft   = $this->theme->variables()->get('padding.left', 1);
        $themePaddingRight  = $this->theme->variables()->get('padding.right', 1);

        $this->properties->set('padding.left', intval($value / 2 * $themePaddingLeft * $themePaddingGlobal));
        $this->properties->set('padding.right', intval($value / 2 * $themePaddingRight * $themePaddingGlobal));

        return $this;
    }

    /**
     * Set element padding left property.
     *
     * @param int $value Padding left.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function pl(int $value): self
    {
        $themePaddingGlobal = $this->theme->variables()->get('padding.global', 1);
        $themePaddingLeft   = $this->theme->variables()->get('padding.left', 1);

        $this->properties->set('padding.left', intval($value * $themePaddingLeft * $themePaddingGlobal));

        return $this;
    }

    /**
     * Set element padding right property.
     *
     * @param int $value Padding right.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function pr(int $value): self
    {
        $themePaddingGlobal = $this->theme->variables()->get('padding.global', 1);
        $themePaddingRight  = $this->theme->variables()->get('padding.right', 1);

        $this->properties->set('padding.right', intval($value * $themePaddingRight * $themePaddingGlobal));

        return $this;
    }

    /**
     * Limit the number of characters in the element value.
     *
     * @param  int    $limit  Limit of characters.
     * @param  string $append Text to append to the string IF it gets truncated.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function limit(int $limit = 100, string $append = '...'): self
    {
        $this->value->limit($limit, $append);

        return $this;
    }

    /**
     * Convert element value to lower-case.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function lower(): self
    {
        $this->value->lower();

        return $this;
    }

    /**
     * Convert element value to upper-case.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function upper(): self
    {
        $this->value->upper();

        return $this;
    }

    /**
     * Repeated element value given a multiplier.
     *
     * @param int $multiplier The number of times to repeat the string.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function repeat(int $multiplier): self
    {
        $this->value->repeat($multiplier);

        return $this;
    }

    /**
     * Convert element value to camel case.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function camel(): self
    {
        $this->value->camel();

        return $this;
    }

    /**
     * Convert element value first character of every word of string to upper case and the others to lower case.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function capitalize(): self
    {
        $this->value->capitalize();

        return $this;
    }

    /**
     * Dynamically bind magic methods to the Element class.
     *
     * @param string $method     Method.
     * @param array  $parameters Parameters.
     *
     * @return mixed Returns mixed content.
     *
     * @throws BadMethodCallException If method not found.
     *
     * @access public
     */
    public function __call(string $method, array $parameters)
    {
        if (strings($method)->startsWith('display')) {
            return $this->display(strings(substr($method, 7))->lower()->toString());
        }

        if (strings($method)->startsWith('bg')) {
            return $this->bg(strings(substr($method, 2))->kebab()->toString());
        }

        if (strings($method)->startsWith('color')) {
            return $this->color(strings(substr($method, 5))->kebab()->toString());
        }

        if (strings($method)->startsWith('mx')) {
            return $this->mx(strings(substr($method, 2))->toInteger());
        }

        if (strings($method)->startsWith('ml')) {
            return $this->ml(strings(substr($method, 2))->toInteger());
        }

        if (strings($method)->startsWith('mr')) {
            return $this->mr(strings(substr($method, 2))->toInteger());
        }

        if (strings($method)->startsWith('px')) {
            return $this->px(strings(substr($method, 2))->toInteger());
        }

        if (strings($method)->startsWith('pl')) {
            return $this->pl(strings(substr($method, 2))->toInteger());
        }

        if (strings($method)->startsWith('pr')) {
            return $this->pr(strings(substr($method, 2))->toInteger());
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }

    /**
     * Render element.
     *
     * @return string Returns rendered element.
     *
     * @access public
     */
    public function render(): string
    {
        $properties = [
            'pl'    => $this->properties->get('padding.left') ?? 0,
            'pr'    => $this->properties->get('padding.right') ?? 0,
            'ml'    => $this->properties->get('margin.left') ?? 0,
            'mr'    => $this->properties->get('margin.right') ?? 0,
            'color' => $this->properties->get('color') ?? null,
            'bg'    => $this->properties->get('bg') ?? null,
        ];

        $padding = static fn ($value) => "[p l={$properties['pl']} r={$properties['pr']}]{$value}[/p]";
        $margin  = static fn ($value) => "[m l={$properties['ml']} r={$properties['mr']}]{$value}[/m]";
        $color   = static fn ($value) => $properties['color'] ? "[color={$properties['color']}]{$value}[/color]" : $value;
        $bg      = static fn ($value) => $properties['bg'] ? "[bg={$properties['bg']}]{$value}[/bg]" : $value;

        return $margin($bg($color($padding((string) $this->value))));
    }

    /**
     * Display element.
     *
     * @param string $type Display type.
     *
     * @throws BadMethodCallException If method not found.
     *
     * @access public
     */
    public function display(string $type = 'row')
    {
        switch ($type) {
            case 'none':
                $this->output->write('');
                break;

            case 'col':
                $render = $this->shortcodes->parse($this->render());
                $this->output->write($render);
                break;

            case 'row':
            default:
                $render = $this->shortcodes->parse($this->render());
                $this->output->writeln($render);
                break;
        }
    }

    /**
     * Get element as string.
     *
     * @return string Returns element string representation.
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
