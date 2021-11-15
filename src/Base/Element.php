<?php

declare(strict_types=1);

/**
 * Termage - Totally RAD Terminal styling for PHP! (https://digital.flextype.org/termage/)
 * Copyright (c) Sergey Romanenko (https://awilum.github.io)
 *
 * Licensed under The MIT License.
 *
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 */

namespace Termage\Base;

use Termage\Termage;
use Atomastic\Arrays\Arrays as Collection;
use Atomastic\Strings\Strings;
use BadMethodCallException;
use Termage\Parsers\Shortcodes;
use Termage\Themes\Theme;
use Termage\Themes\ThemeInterface;
use Termage\Base\Styles;
use Termage\Base\Color;

use function abs;
use function arrays as collection;
use function intval;
use function is_null;
use function preg_replace;
use function sprintf;
use function strings;
use function Termage\color;
use function Termage\getCsi;
use function Termage\terminal;

use const PHP_EOL;

abstract class Element
{
    /**
     * Element value.
     *
     * @access private
     */
    private string $value;

    /**
     * Element classes.
     *
     * @access private
     */
    private string $classes;

    /**
     * Element styles.
     *
     * @access private
     */
    private Collection $styles;

    /**
     * The implementation of Theme interface.
     *
     * @access private
     */
    private static ThemeInterface $theme;

    /**
     * The instance of Shortcodes class.
     */
    private static Shortcodes $shortcodes;

    /**
     * Registered element classes.
     */
    private Collection $registeredClasses;

    /**
     * Force element to self-clear its children block elements linebreaks.
     *
     * @access private
     */
    private bool $clearfix;

    /**
     * Create a new Element instance.
     *
     * @param        $theme
     * @param        $shortcodes
     * @param string     $value   Element value.
     * @param string     $classes Element classes.
     *
     * @return Element Returns element.
     *
     * @access public
     */
    final public function __construct(
        $theme = null,
        $shortcodes = null,
        string $value = '',
        string $classes = ''
    ) {
        self::$theme             = $theme ??= new Theme();
        self::$shortcodes        = $shortcodes ??= new Shortcodes(self::getTheme());
        $this->value             = $value;
        $this->classes           = $classes;
        $this->registeredClasses = collection($this->getDefaultClasses())->merge($this->getElementClasses(), true);
        $this->styles            = collection();
        $this->clearfix          = false;
    }

    /**
     * Get element value.
     *
     * @return Strings Returns element value.
     *
     * @access public
     */
    public function getValue(): string
    {
        return $this->value;
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
        $this->value = $value;

        return $this;
    }

    /**
     * Get element styles.
     *
     * @return Collection Returns element styles.
     *
     * @access public
     */
    public function getStyles(): Collection
    {
        return $this->styles;
    }

    /**
     * Set element styles.
     *
     * @param string $styles Element styles.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function styles(array $styles = []): self
    {
        $this->styles = collection($styles);

        return $this;
    }

    /**
     * Get element classes.
     *
     * @return string Returns Element classes.
     *
     * @access public
     */
    public function getClasses(): string
    {
        return $this->classes;
    }

    /**
     * Set element classes.
     *
     * @param string $classes Element classes.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function classes(string $classes = ''): self
    {
        $this->classes = $classes;

        return $this;
    }

    /**
     * Get Shortcodes instance.
     *
     * @return Shortcodes Shortcodes instance.
     *
     * @access public
     */
    public static function getShortcodes(): Shortcodes
    {
        return self::$shortcodes ??= new Shortcodes(self::getTheme());
    }

    /**
     * Set a new instance of the shortcodes.
     *
     * @param Shortcodes $shortcodes Shortcodes instance.
     *
     * @access public
     */
    public static function setShortcodes(Shortcodes $shortcodes): void
    {
        self::$shortcodes = $shortcodes;
    }

    /**
     * Get instance of the theme that implements Themes interface.
     *
     * @return ThemeInterface Returns instance of the theme that implements Themes interface.
     *
     * @access public
     */
    public static function getTheme(): ThemeInterface
    {
        return self::$theme ??= new Theme();
    }

    /**
     * Set a new instance of the theme that implements Themes interface.
     *
     * @param ThemeInterface $theme Theme interface.
     *
     * @access public
     */
    public static function setTheme(ThemeInterface $theme): void
    {
        self::$theme = $theme;
    }

    /**
     * Get default element classes.
     *
     * @return array Array of default classes.
     *
     * @access public
     */
    final public function getDefaultClasses(): array
    {
        return [
            'bold',
            'italic',
            'bg',
            'color',
            'dim',
            'strikethrough',
            'invisible',
            'underline',
            'reverse',
            'blink',
            'text-align',
            'clearfix',
            'b',
            'bColor',
            'p',
            'pl',
            'pr',
            'px',
            'py',
            'pt',
            'pb',
            'm',
            'ml',
            'mr',
            'mx',
            'my',
            'mt',
            'mb',
            'w',
            'h',
            'd',
        ];
    }

    /**
     * Get element classes.
     *
     * @return array Array of element classes.
     *
     * @access public
     */
    public function getElementClasses(): array
    {
        return [];
    }

    /**
     * Set element bold style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function bold(): self
    {
        $this->styles->set('bold', true);

        return $this;
    }

    /**
     * Set element italic style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function italic(): self
    {
        $this->styles->set('italic', true);

        return $this;
    }

    /**
     * Set element strikethrough style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function strikethrough(): self
    {
        $this->styles->set('strikethrough', true);

        return $this;
    }

    /**
     * Set element dim style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function dim(): self
    {
        $this->styles->set('dim', true);

        return $this;
    }

    /**
     * Set element underline style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function underline(): self
    {
        $this->styles->set('underline', true);

        return $this;
    }

    /**
     * Set element blink style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function blink(): self
    {
        $this->styles->set('blink', true);

        return $this;
    }

    /**
     * Set element reverse style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function reverse(): self
    {
        $this->styles->set('reverse', true);

        return $this;
    }

    /**
     * Set element invisible style.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function invisible(): self
    {
        $this->styles->set('invisible', true);

        return $this;
    }

    /**
     * Set element text color style.
     *
     * @param string $color Element text color value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function color(string $color): self
    {
        $this->styles->set('color', $color);

        return $this;
    }

    /**
     * Set element background color style.
     *
     * @param string $color Element background color value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function bg(string $color): self
    {
        $this->styles->set('bg', $color);

        return $this;
    }

    /**
     * Set element margin top, right, bottom, left style.
     *
     * @param int      $top    Margin top value.
     * @param int|null $right  Margin right value.
     * @param int|null $bottom Margin bottom value.
     * @param int|null $left   Margin left value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function m(int $top, ?int $right = null, ?int $bottom = null, ?int $left = null): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        if (is_null($right) && is_null($bottom) && is_null($left)) {
            $right  = $top;
            $bottom = $top;
            $left   = $top;
        }

        $this->styles->set('margin.top', intval($top * $themeSpacer));
        $this->styles->set('margin.right', intval($right * $themeSpacer));
        $this->styles->set('margin.bottom', intval($bottom * $themeSpacer));
        $this->styles->set('margin.left', intval($left * $themeSpacer));

        return $this;
    }

    /**
     * Set element margin x style.
     *
     * @param int $value Margin x value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function mx(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('margin.left', intval($value * $themeSpacer));
        $this->styles->set('margin.right', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element margin y style.
     *
     * @param int $value Margin y value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function my(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('margin.top', intval($value * $themeSpacer));
        $this->styles->set('margin.bottom', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element margin top style.
     *
     * @param int $value Margin top value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function mt(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('margin.top', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element margin bottom style.
     *
     * @param int $value Margin bottom value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function mb(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('margin.bottom', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element margin left style.
     *
     * @param int $value Margin left value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function ml(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('margin.left', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element margin right style.
     *
     * @param int $value Margin right value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function mr(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('margin.right', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element padding x style.
     *
     * @param int $value Padding x value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function px(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('padding.left', intval($value * $themeSpacer));
        $this->styles->set('padding.right', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element padding y style.
     *
     * @param int $value Padding y value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function py(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('padding.top', intval($value * $themeSpacer));
        $this->styles->set('padding.bottom', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element padding top style.
     *
     * @param int $value Padding top value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function pt(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('padding.top', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element padding bottom style.
     *
     * @param int $value Padding bottom value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function pb(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('padding.bottom', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element padding top, right, bottom, left style.
     *
     * @param int      $top    Padding top value.
     * @param int|null $right  Padding right value.
     * @param int|null $bottom Padding bottom value.
     * @param int|null $left   Padding left value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function p(int $top, ?int $right = null, ?int $bottom = null, ?int $left = null): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        if (is_null($right) && is_null($bottom) && is_null($left)) {
            $right  = $top;
            $bottom = $top;
            $left   = $top;
        }

        $this->styles->set('padding.top', intval($top * $themeSpacer));
        $this->styles->set('padding.right', intval($right * $themeSpacer));
        $this->styles->set('padding.bottom', intval($bottom * $themeSpacer));
        $this->styles->set('padding.left', intval($left * $themeSpacer));

        return $this;
    }

    /**
     * Set element padding left style.
     *
     * @param int $value Padding left value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function pl(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('padding.left', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element padding right style.
     *
     * @param int $value Padding right value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function pr(int $value): self
    {
        $themeSpacer = self::$theme->getVariables()->get('spacer', 1);

        $this->styles->set('padding.right', intval($value * $themeSpacer));

        return $this;
    }

    /**
     * Set element text align style.
     *
     * @param mixed $value Text align value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function textAlign(string $value): self
    {
        $this->styles->set('text-align', $value);

        return $this;
    }

    /**
     * Set element width style.
     *
     * @param mixed $value Width value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function w($value): self
    {
        $this->styles->set('width', $value);

        return $this;
    }

    /**
     * Set element height style.
     *
     * @param mixed $value Height value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function h($value): self
    {
        $this->styles->set('height', $value);

        return $this;
    }

    /**
     * Set element display style.
     *
     * @param string $value Display value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function d(string $value): self
    {
        $this->styles->set('display', $value);

        return $this;
    }

    /**
     * Set element border style.
     *
     * @param string $value Border style value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function b(string $value): self
    {
        $this->styles->set('border', $value);

        return $this;
    }

    /**
     * Set element border color style.
     *
     * @param string $value Border color style value.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function bColor(string $value): self
    {
        $this->styles->set('border-color', $value);

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
        if (strings($method)->startsWith('b')) {
            if (strings($method)->startsWith('bg')) {
                return $this->bg(strings($method)->substr(2)->kebab()->toString());
            }

            if (strings($method)->startsWith('bColor')) {
                return $this->bColor(strings($method)->substr(6)->kebab()->toString());
            }

            if (strings($method)->substr(1)->toString()) {
                return $this->b(strings($method)->substr(1)->kebab()->toString());
            }
        }

        if (strings($method)->startsWith('color')) {
            return $this->color(strings($method)->substr(5)->kebab()->toString());
        }

        if (strings($method)->startsWith('m')) {
            if (strings($method)->startsWith('mx')) {
                return $this->mx(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('my')) {
                return $this->my(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('ml')) {
                return $this->ml(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('mr')) {
                return $this->mr(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('mt')) {
                return $this->mt(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('mb')) {
                return $this->mb(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->substr(1)->toInteger()) {
                return $this->m(strings($method)->substr(1)->toInteger());
            }

            return $this->m(...$parameters);
        }

        if (strings($method)->startsWith('p')) {
            if (strings($method)->startsWith('px')) {
                return $this->px(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('py')) {
                return $this->py(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('pl')) {
                return $this->pl(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('pr')) {
                return $this->pr(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('pt')) {
                return $this->pt(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->startsWith('pb')) {
                return $this->pb(strings($method)->substr(2)->toInteger());
            }

            if (strings($method)->substr(1)->toInteger()) {
                return $this->p(strings($method)->substr(1)->toInteger());
            }

            return $this->p(...$parameters);
        }

        if (strings($method)->startsWith('d')) {
            return $this->d(strings($method)->substr(1)->kebab()->toString());
        }

        if (strings($method)->startsWith('textAlign')) {
            return $this->textAlign(strings($method)->substr(9)->kebab()->toString());
        }

        if (strings($method)->startsWith('w')) {
            if ($method === 'wAuto') {
                return $this->w('auto');
            }

            return $this->w(strings($method)->substr(1)->toInteger());
        }

        if (strings($method)->startsWith('h')) {
            return $this->h(strings($method)->substr(1)->toInteger());
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }

    /**
     * Force element to self-clear its children block elements linebreaks.
     *
     * @return self Returns instance of the Element class.
     *
     * @access public
     */
    public function clearfix(): self
    {
        $this->clearfix = true;

        return $this;
    }

    /**
     * Process element classes.
     *
     * @access public
     */
    public function processClasses(): void
    {
        $classes = strings($this->classes)->trim();

        if ($classes->length() <= 0) {
            return;
        }

        foreach ($classes->segments() as $class) {
            $methodName = (string) strings($class)->camel()->trim();
            foreach ($this->registeredClasses->toArray() as $registeredClass) {
                $registeredClassName = (string) strings($registeredClass)->camel()->trim();

                if (! strings($methodName)->startsWith($registeredClassName)) {
                    continue;
                }

                $this->{$methodName}();
            }
        }
    }

    /**
     * Process styles for element value.
     *
     * @access public
     */
    public function processStyles(): void
    {
        // Termage box model and styles hierarchy.
        //
        // ┌─────────────────────────────────────────────────────────────┐
        // │ display                                                     │
        // │ ┌─────────────────────────────────────────────────────────┐ │
        // │ │ outer: margins                                          | │ 
        // │ │ ┌─────────────────────────────────────────────────────┐ │ │
        // | | | borders                                             | │ │
        // │ │ | ┌─────────────────────────────────────────────────┐ │ │ │
        // │ │ │ │ bg, color                                       │ │ │ │
        // │ │ │ │ ┌─────────────────────────────────────────────┐ │ │ │ │
        // │ │ │ │ │ inner: width, height paddings               │ │ │ │ │
        // │ │ │ │ │ ┌─────────────────────────────────────────┐ │ │ │ │ │
        // │ │ │ │ │ │ invisible, reverse, blink, dim, bold,   │ │ │ │ │ │
        // │ │ │ │ │ │ italic, underline, strikethrough.       │ │ │ │ │ │
        // | | | | | └─────────────────────────────────────────┘ │ │ │ │ │
        // │ │ │ │ └─────────────────────────────────────────────┘ │ │ │ │
        // │ │ │ └─────────────────────────────────────────────────┘ │ │ │
        // | | └─────────────────────────────────────────────────────┘ │ │
        // │ └─────────────────────────────────────────────────────────┘ │
        // └─────────────────────────────────────────────────────────────┘
        $stylesHierarchy = ['invisible', 'reverse', 'blink', 'dim', 'bold', 'italic', 'underline', 'strikethrough', 'inner', 'bg', 'color', 'outer', 'display'];

        // Process style: outer
        $outer = function ($value) {
            $ml = $this->styles->get('margin.left') ?? 0;
            $mr = $this->styles->get('margin.right') ?? 0;
            $mt = $this->styles->get('margin.top') ?? 0;
            $mb = $this->styles->get('margin.bottom') ?? 0;
            $pl = $this->styles->get('padding.left') ?? 0;
            $pr = $this->styles->get('padding.right') ?? 0;

            // Do not allow margins for block elements with clearfix flag.
            if ($this->clearfix) {
                return $value;
            }

            // Do not allow vertical margins for inline elements.
            if ($this->styles->get('display') === 'inline') {
                $mt = 0;
                $mb = 0;
            }

            return // Set margin top
                    ($mt > 0 ? strings(PHP_EOL)->repeat($mt) : '') .

                    // Set margin left, only padding left is not set.
                    ($ml > 0 && $pl < 0 ? strings(' ')->repeat($ml) : '') .

                    // Set Value
                    $value .

                    // Set margin right, only padding right is not set.
                    ($mr > 0 && $pr < 0 ? strings(' ')->repeat($mr) : '') .

                    // Set margin bottom
                    ($mb > 0 ? strings(PHP_EOL)->repeat($mb) : '');
        };

        // Process style: inner
        $inner = function ($value) {
            $valueLength    = $this->getLength($value);
            $textAlignStyle = $this->styles->get('text-align') ?? 'left';
            $widthStyle     = $this->styles->get('width') ?? 'auto';
            $heightStyle    = $this->styles->get('height') ?? 'auto';
            $displayStyle   = $this->styles->get('display') ?? 'block';
            $borderStyle    = $this->styles->get('border') ?? 'none';
            $pl             = $this->styles->get('padding.left') ?? 0;
            $pr             = $this->styles->get('padding.right') ?? 0;
            $pt             = $this->styles->get('padding.top') ?? 0;
            $pb             = $this->styles->get('padding.bottom') ?? 0;
            $ml             = $this->styles->get('margin.left') ?? 0;
            $mr             = $this->styles->get('margin.right') ?? 0;
            $spaces         = 0;
            $borderSpaces   = 2;

            // Helper function for re-apply text and background colors.
            $applyTextAndBackgroundColor = function ($value) {
                $bg    = $this->styles->get('bg') ? self::$theme->getVariables()->get('colors.' . $this->styles->get('bg'), $this->styles->get('bg')) : false;
                $color = $this->styles->get('color') ? self::$theme->getVariables()->get('colors.' . $this->styles->get('color'), $this->styles->get('color')) : false;

                $value = ($color ? Color::applyForegroundColor($value, $color) : $value);
                $value = ($bg ? Color::applyBackgroundColor($value, $bg) : $value);

                return $value;
            };

            // Helper function for apply border color.
            $applyBorderColor = function ($value) {
                $color = $this->styles->get('border-color') ? self::$theme->getVariables()->get('colors.' . $this->styles->get('border-color'), $this->styles->get('border-color')) : false;

                $value = ($color ? Color::applyForegroundColor($value, $color) : $value);

                return $value;
            };

            // Helper function for determine is border exist.
            $hasBorder = function () use ($borderStyle) {
                return $borderStyle !== 'none' && self::$theme->getVariables()->has('borders.' . $borderStyle);
            };

            // Helper function for adding paddings and borders, top and bottom
            $addPaddingsAndBorders = function ($valueSpaces) use ($borderStyle, $hasBorder, $applyBorderColor, $applyTextAndBackgroundColor, $pt, $pb, $ml, $mr) {

                    // Create box border top value.
                    $btStyleValue = '';
                    if ($hasBorder()) {
                        $btStyleValue = Styles::resetAll() . 
                                        strings(' ')->repeat($ml) . 
                                        $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.top-left')) . 
                                        $applyBorderColor((string) strings(self::$theme->getVariables()->get('borders.' . $borderStyle . '.top'))->repeat($valueSpaces)) .
                                        $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.top-right')) .  
                                        Styles::resetAll() . 
                                        PHP_EOL;
                    }

                    // Create box border bottom value.
                    $bbStyleValue = PHP_EOL;
                    if ($hasBorder()) {
                        $bbStyleValue = Styles::resetAll() . 
                                        strings(' ')->repeat($ml) . 
                                        $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.bottom-left')) . 
                                        $applyBorderColor((string) strings(self::$theme->getVariables()->get('borders.' . $borderStyle . '.bottom'))->repeat($valueSpaces)) . 
                                        $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.bottom-right')) . 
                                        Styles::resetAll();
                    }

                    // Create box padding top value.
                    $ptStyleValue = '';
                    for ($i = 0; $i < $pt; $i++) {
                        $ptStyleValue .= Styles::resetAll() .
                                         strings(' ')->repeat($ml) . 
                                         ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') . 
                                         $applyTextAndBackgroundColor((string) strings(' ')->repeat($valueSpaces)) . 
                                         ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') . 
                                         Styles::resetAll() . 
                                         PHP_EOL;
                    }

                    // Create box padding bottom value.
                    $pbStyleValue = PHP_EOL;
                    for ($i = 0; $i < $pb; $i++) {
                        $pbStyleValue .= Styles::resetAll() . 
                                        strings(' ')->repeat($ml) . 
                                        ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') . 
                                        $applyTextAndBackgroundColor((string) strings(' ')->repeat($valueSpaces)) . 
                                        ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') . 
                                        Styles::resetAll() . 
                                        PHP_EOL;
                    }

                    return ['bt' => $btStyleValue, 'bb' => $bbStyleValue, 'pt' => $ptStyleValue, 'pb' => $pbStyleValue];
            };

            // Calculate height
            // Update paddings top and bottom
            if ($heightStyle !== 'auto' && $heightStyle > 0) {
                $pt = intval($heightStyle / 2) - ($heightStyle % 2 === 0 ? 1 : 0);
                $pb = intval($heightStyle / 2);
            }

            // Set block element with auto width
            if ($widthStyle === 'auto' && $displayStyle === 'block') {

                // Do not allow width for block elements with clearfix flag.
                if ($this->clearfix) {
                    return $value;
                }
                
                // Calculate amount of available spaces for block element.
                $spaces = abs(terminal()->getwidth() - $valueLength);

                // Text align left
                if ($textAlignStyle === 'left') {

                    $paddingsAndBorders = $addPaddingsAndBorders($spaces + $valueLength - $mr - $ml - ($hasBorder() ? $borderSpaces : 0));

                    return  // Set box border top style.
                            ($borderStyle !== 'none' ? $paddingsAndBorders['bt'] : '') . 

                            // Set box padding top.
                            ($pt > 0 ? $paddingsAndBorders['pt'] : '') .

                            // Set box margin left, 
                            // paddings left and right, 
                            // re-apply text and background colors, 
                            // apply borders.
                            Styles::resetAll() . 
                            strings(' ')->repeat($ml) .
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') . 
                            $applyTextAndBackgroundColor(strings(' ')->repeat($pl) .
                                                        $value .
                                                        strings(' ')->repeat($spaces - $pl - $ml - $mr - ($hasBorder() ? $borderSpaces : 0))) .
                                                    
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') . 

                            // Set box padding bottom.
                            ($pb > 0 ? strings($paddingsAndBorders['pb'])->trimRight(PHP_EOL) : '') .

                            // Set box border top style.
                            ($hasBorder() ? strings(' ')->repeat($ml) . $paddingsAndBorders['bb'] : '');

                }

                // Text align right
                if ($textAlignStyle === 'right') {

                    $paddingsAndBorders = $addPaddingsAndBorders($spaces + $valueLength - $mr - $ml - ($hasBorder() ? $borderSpaces : 0));

                    return  // Set box border top style.
                            ($borderStyle !== 'none' ? $paddingsAndBorders['bt'] : '') . 

                            // Set box padding top.
                            ($pt > 0 ? $paddingsAndBorders['pt'] : '') .
                    
                            // Set box margin left, 
                            // paddings left and right, 
                            // re-apply text and background colors, 
                            // apply borders.
                            Styles::resetAll() . 
                            strings(' ')->repeat($ml) .
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') . 
                            $applyTextAndBackgroundColor(strings(' ')->repeat($spaces - $pr - $ml - $mr - ($hasBorder() ? $borderSpaces : 0)) .
                                                        $value .
                                                        strings(' ')->repeat($pr)) .

                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') .

                            // Set box padding bottom.
                            ($pb > 0 ? strings($paddingsAndBorders['pb'])->trimRight(PHP_EOL) : '') . 

                            // Set box border top style.
                            ($hasBorder() ? strings(' ')->repeat($ml) . $paddingsAndBorders['bb'] : '');
                }

                // Text align center
                if ($textAlignStyle === 'center') {

                    $paddingsAndBorders = $addPaddingsAndBorders($spaces + $valueLength - $mr - $ml - ($hasBorder() ? $borderSpaces : 0));

                    // Calculate left and right spaces.
                    $leftSpaces  = intval($spaces / 2);
                    $rightSpaces = intval($spaces / 2);

                    // Normalize left spaces.
                    if (intval($leftSpaces * 2) < $spaces) {
                        $leftSpaces++;
                    }

                    // Calculate spaces for current box.
                    $currentSpaces = $leftSpaces - $ml + $rightSpaces - $mr;

                    // Calculate left spaces for current box.
                    $currentLeftSpaces  = intval($currentSpaces / 2);
                    $currentRightSpaces = intval($currentSpaces / 2);

                    // Normalize left spaces for current box.
                    if (intval($currentLeftSpaces * 2) < $currentSpaces) {
                        $currentLeftSpaces++;
                    }

                    return // Set box border top style.
                            ($borderStyle !== 'none' ? $paddingsAndBorders['bt'] : '') . 

                            // Set box padding top.
                            ($pt > 0 ? $paddingsAndBorders['pt'] : '') .

                            // Set box margin left, 
                            // paddings left and right, 
                            // re-apply text and background colors, 
                            // apply borders.                            Styles::resetAll() . 
                            strings(' ')->repeat($ml) .
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') . 
                            $applyTextAndBackgroundColor(strings(' ')->repeat($currentLeftSpaces - ($hasBorder() ? $borderSpaces : 0)) .
                                                        $value .
                                                        strings(' ')->repeat($currentRightSpaces)) .

                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') .

                            // Set box padding bottom.
                            ($pb > 0 ? strings($paddingsAndBorders['pb'])->trimRight(PHP_EOL) : '') . 

                            // Set box border top style.
                            ($hasBorder() ? strings(' ')->repeat($ml) . $paddingsAndBorders['bb'] : '');
                }
            }

            // Display block with custom width
            if ($widthStyle !== 'auto' && $displayStyle === 'block') {

                $spaces = $widthStyle - $valueLength < $valueLength ? 0 : $widthStyle - $valueLength;

                // Text align left
                if ($textAlignStyle === 'left') {

                    $paddingsAndBorders = $addPaddingsAndBorders($spaces + $valueLength + $pl + $pr - ($hasBorder() ? $borderSpaces : 0));

                    return  // Set box border top style.
                            ($borderStyle !== 'none' ? $paddingsAndBorders['bt'] : '') . 

                            // Set box padding top.
                            ($pt > 0 ? $paddingsAndBorders['pt'] : '') .

                            // Set box margin left, 
                            // paddings left and right, 
                            // re-apply text and background colors, 
                            // apply borders.
                            Styles::resetAll() . 
                            strings(' ')->repeat($ml) .
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') . 
                            $applyTextAndBackgroundColor(strings(' ')->repeat($pl) .
                                                        $value .
                                                        strings(' ')->repeat($spaces + $pr - ($hasBorder() ? $borderSpaces : 0))) .

                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') .

                            // Set box padding bottom.
                            ($pb > 0 ? strings($paddingsAndBorders['pb'])->trimRight(PHP_EOL) : '') . 

                            // Set box border top style.
                            ($hasBorder() ? strings(' ')->repeat($ml) . PHP_EOL . $paddingsAndBorders['bb'] : '');
                }

                // Text align right
                if ($textAlignStyle === 'right') {
                    
                    $paddingsAndBorders = $addPaddingsAndBorders($spaces + $valueLength + $pl + $pr - ($hasBorder() ? $borderSpaces : 0));

                    return  // Set box border top style.
                            ($borderStyle !== 'none' ? $paddingsAndBorders['bt'] : '') . 

                            // Set box padding top.
                            ($pt > 0 ? $paddingsAndBorders['pt'] : '') .

                            // Set box margin left, 
                            // paddings left and right, 
                            // re-apply text and background colors, 
                            // apply borders.
                            Styles::resetAll() . 
                            strings(' ')->repeat($ml) .
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') .
                            $applyTextAndBackgroundColor(strings(' ')->repeat($spaces + $pl - ($hasBorder() ? $borderSpaces : 0)) .
                                                        $value .
                                                        strings(' ')->repeat($pr)) .

                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') .

                            // Set box padding bottom.
                            ($pb > 0 ? strings($paddingsAndBorders['pb'])->trimRight(PHP_EOL) : '') . 

                            // Set box border top style.
                            ($hasBorder() ? strings(' ')->repeat($ml) . PHP_EOL . $paddingsAndBorders['bb'] : '');
                }

                // Text align center
                if ($textAlignStyle === 'center') {
                
                    // Calculate left and right spaces.
                    $leftSpaces  = intval($spaces / 2);
                    $rightSpaces = intval($spaces / 2);

                    // Normalize left spaces.
                    if (intval($leftSpaces * 2) < $spaces) {
                        $leftSpaces++;
                    }

                    $paddingsAndBorders = $addPaddingsAndBorders($spaces + $valueLength + $pl + $pr - ($hasBorder() ? $borderSpaces : 0));

                    return  // Set box border top style.
                            ($borderStyle !== 'none' ? $paddingsAndBorders['bt'] : '') . 

                            // Set box padding top.
                            ($pt > 0 ? $paddingsAndBorders['pt'] : '') .

                            // Set box margin left, 
                            // paddings left and right, 
                            // re-apply text and background colors, 
                            // apply borders.
                            Styles::resetAll() .
                            strings(' ')->repeat($ml) .
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.left')) : '') .
                            $applyTextAndBackgroundColor(strings(' ')->repeat($leftSpaces + $pl - ($hasBorder() ? $borderSpaces / 2 : 0)) .
                                                            $value .
                                                            strings(' ')->repeat($rightSpaces + $pr - ($hasBorder() ? $borderSpaces / 2 : 0))) .
                            
                            ($hasBorder() ? $applyBorderColor(self::$theme->getVariables()->get('borders.' . $borderStyle . '.right')) : '') .

                            // Set box padding bottom.
                            ($pb > 0 ? strings($paddingsAndBorders['pb'])->trimRight(PHP_EOL) : '') . 

                            // Set box border top style.
                            ($hasBorder() ? strings(' ')->repeat($ml) . PHP_EOL . $paddingsAndBorders['bb'] : '');;
                }
            }

            // Display inline
            if ($displayStyle === 'inline') {
                return strings(' ')->repeat($pl) . $value . strings(' ')->repeat($pr);
            }
        };

        // Process style: color
        $color = function ($value) {
            $color = $this->styles->get('color') ? self::$theme->getVariables()->get('colors.' . $this->styles->get('color'), $this->styles->get('color')) : false;

            return $color ? Color::applyForegroundColor($value, $color) : $value;
        };

        // Process style: bg
        $bg = function ($value) {
            $bg = $this->styles->get('bg') ? self::$theme->getVariables()->get('colors.' . $this->styles->get('bg'), $this->styles->get('bg')) : false;

            return $bg ? Color::applyBackgroundColor($value, $bg) : $value;
        };

        // Process style: bold
        $bold = function ($value) {
            return $this->styles['bold'] ? Styles::setBold() . $value . Styles::resetBold() : $value;
        };

        // Process style: italic
        $italic = function ($value) {
            return $this->styles['italic'] ? Styles::setItalic() . $value . Styles::resetItalic() : $value;
        };

        // Process style: underline
        $underline = function ($value) {
            return $this->styles['underline'] ? Styles::setUnderline() . $value . Styles::resetUnderline() : $value;
        };

        // Process style: strikethrough
        $strikethrough = function ($value) {
            return $this->styles['strikethrough'] ? Styles::setStrikethrough() . $value . Styles::resetStrikethrough() : $value;
        };

        // Process style: dim
        $dim = function ($value) {
            return $this->styles['dim'] ? Styles::setDim() . $value . Styles::resetDim() : $value;
        };

        // Process style: blink
        $blink = function ($value) {
            return $this->styles['blink'] ? Styles::setBlink() . $value . Styles::resetBlink() : $value;
        };

        // Process style: reverse
        $reverse = function ($value) {
            return $this->styles['reverse'] ? Styles::setReverse() . $value . Styles::resetReverse() : $value;
        };

        // Process style: invisible
        $invisible = function ($value) {
            return $this->styles['invisible'] ? Styles::setInvisible() . $value . Styles::resetInvisible() : $value;
        };

        // Process style: display
        $display = function ($value) {
            $displayStyle = $this->styles->get('display') ?? 'block';

            switch ($displayStyle) {
                case 'inline':
                    return $value;

                    break;
                case 'none':
                    return '';

                    break;
                case 'block':
                default:
                    // Do not add linebreak for block elements if it has clearfix flag.
                    if ($this->clearfix) {
                        $result = $value;
                    } else {
                        $result = $value . PHP_EOL;
                    }

                    return $result;

                    break;
            }
        };

        // Process styles accorind to box model based on styles hierarchy.
        foreach ($stylesHierarchy as $propertyName) {
            $this->value = ${$propertyName}($this->value);
        }
    }

    /**
     * Process shortcodes for element value.
     *
     * @access public
     */
    public function processShortcodes(): void
    {
        $this->value = self::$shortcodes->parse($this->value);
    }

    /**
     * Strip styles.
     *
     * @param string $value Value with styles.
     *
     * @return string Value without styles.
     *
     * @access public
     */
    public function stripStyles(string $value): string
    {
        return preg_replace("/\e\[[^m]*m/", '', $value);
    }

    /**
     * Strip all decorations.
     *
     * @param string $value Value with decorations.
     *
     * @return string Value without decorations.
     *
     * @access public
     */
    public function stripDecorations(string $value): string
    {
        return self::getShortcodes()->stripShortcodes($this->stripStyles($value));
    }

    /**
     * Get value length without decorations and line breaks.
     *
     * @param string $value Value.
     *
     * @return int Value length without decorations and line breaks.
     *
     * @access public
     */
    public function getLength(string $value): int
    {
        return strings($this->stripDecorations($value))->replace(PHP_EOL, '')->length();
    }

    /**
     * Get rendered element.
     *
     * @return string Returns rendered element.
     *
     * @access public
     */
    public function render(): string
    {
        $this->processClasses();
        $this->processStyles();
        $this->processShortcodes();

        return $this->value;
    }

    /**
     * Get rendered element as string representation.
     *
     * @return string Returns rendered element as string representation.
     *
     * @access public
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
