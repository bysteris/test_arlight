<?php
declare(strict_types=1);

/**
 * БАЗОВЫЙ АБСТРАКТНЫЙ КЛАСС HTML элемента
 * Содержит общие свойства и логику для всех HTML эл-ов
 * Каждый конкретный элемент обязан реализовать метод render().
 */
abstract class HtmlElement
{
    // Базовые HTMLатрибуты, доступные всем эл-ам
    protected ?string $id;
    protected ?string $class;

    // Конструктор базового элемента принимает id и класс
    public function __construct(?string $id = null, ?string $class = null)
    {
        $this->id = $id;
        $this->class = $class;
    }

    /**
     * Генерация атрибутов айди и класс
     * добавил htmlspecilachars, чтобы сразу предусмотреть защиту в полях
     */
    protected function renderBaseAttributes(): string
    {
        $attributes = '';

        if ($this->id !== null) {
            $attributes .= ' id="' . htmlspecialchars($this->id, ENT_QUOTES) . '"';
        }

        if ($this->class !== null) {
            $attributes .= ' class="' . htmlspecialchars($this->class, ENT_QUOTES) . '"';
        }

        return $attributes;
    }

    // Абстрактный метод render, который должны реализовать все потомки
    abstract public function render(): string;
}

/**
 * Трейт: EditableTrait
 * Добавляет функциональность редактируемых элементов:
 * input, textarea и т.д.
 */
trait EditableTrait
{
    protected ?string $name = null;  // имя поля
    protected mixed $value = null; // текущее значение
    protected ?string $placeholder = null; // подсказка для пользователя
    protected bool $required = false; // обязательность поля

    // установка имени поля
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    // установка значения поля
    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    // установка плейсхолдера
    public function setPlaceholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    // утановка обязательности поля
    public function setRequired(bool $required = true): static
    {
        $this->required = $required;
        return $this;
    }

     /**
     * Генерация атрибутов для редактируемого элемента
     * name, value, placeholder, required
     */
    protected function renderEditableAttributes(): string
    {
        $attributes = '';

        if ($this->name !== null) {
            $attributes .= ' name="' . htmlspecialchars($this->name, ENT_QUOTES) . '"';
        }

        if ($this->value !== null) {
            $attributes .= ' value="' . htmlspecialchars((string)$this->value, ENT_QUOTES) . '"';
        }

        if ($this->placeholder !== null) {
            $attributes .= ' placeholder="' . htmlspecialchars($this->placeholder, ENT_QUOTES) . '"';
        }

        if ($this->required) {
            $attributes .= ' required';
        }

        return $attributes;
    }
}

/**
 * Трейт: NumberFormatTrait
 */
trait NumberFormatTrait
{
    protected int $decimals = 2; // кол-во знаков после запятой

    // установка знаков после запятой
    public function setDecimals(int $decimals): static
    {
        $this->decimals = $decimals;
        return $this;
    }

    // Форматирование числа с пробелом для тысяч и точкой для десятичных
    protected function formatNumber(float|int $number): string
    {
        return number_format($number, $this->decimals, '.', ' ');
    }
}

/**
 * InputText текстовое поле
 * Редактируемый input type="text"
 */
class InputText extends HtmlElement
{
    use EditableTrait;

    public function render(): string
    {
        return '<input type="text"' .
            $this->renderBaseAttributes() .
            $this->renderEditableAttributes() .
            '>';
    }
}

/**
 * InputNumber числовое поле
 * Наследует InputText, добавляет:
 * валидацию числа и форматирование через NumberFormatTrait
 */
class InputNumber extends InputText
{
    use NumberFormatTrait;

    // Переопределяю setValue для валидации
    public function setValue(mixed $value): static
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Value must be numeric');
        }

        return parent::setValue($value);
    }

    // Генерация input type="number"
    public function render(): string
    {
        if ($this->value !== null) {
            // форматирую перед выводом
            $this->value = $this->formatNumber((float)$this->value);
        }

        return '<input type="number"' .
            $this->renderBaseAttributes() .
            $this->renderEditableAttributes() .
            '>';
    }
}

/**
 * SpanText статический текстовый элемент 
 *  Не редактируемый элемент, только вывод текста */
class SpanText extends HtmlElement
{
    protected string $content;

    public function __construct(string $content, ?string $id = null, ?string $class = null)
    {
        parent::__construct($id, $class);
        $this->content = $content;
    }

    public function render(): string
    {
        return '<span' .
            $this->renderBaseAttributes() .
            '>' . htmlspecialchars($this->content, ENT_QUOTES) .
            '</span>';
    }
}

/**
 * SpanNumber статический числовой элемент
 * Наследуется от SpanText и добавляет форматирование
 */
class SpanNumber extends SpanText
{
    use NumberFormatTrait;

    public function __construct(float|int $number, ?string $id = null, ?string $class = null)
    {
        parent::__construct((string)$number, $id, $class);
    }

    public function render(): string
    {
        $formatted = $this->formatNumber((float)$this->content);
        return '<span' .
            $this->renderBaseAttributes() .
            '>' . $formatted .
            '</span>';
    }
}

/** Link - ссылка */
class Link extends HtmlElement
{
    protected string $href;
    protected string $text;

    public function __construct(string $href, string $text, ?string $id = null, ?string $class = null)
    {
        parent::__construct($id, $class);
        $this->href = $href;
        $this->text = $text;
    }

    public function render(): string
    {
        return '<a href="' . htmlspecialchars($this->href, ENT_QUOTES) . '"' .
            $this->renderBaseAttributes() .
            '>' . htmlspecialchars($this->text, ENT_QUOTES) .
            '</a>';
    }
}


// Пример использования
$input = (new InputText('input1', 'form-control'))
    ->setName('full_name')
    ->setValue('Иван Иванов') // имя пользователя
    ->setPlaceholder('Введите имя')
    ->setRequired();

echo $input->render() . "<br>";

$number = (new InputNumber('num1'))
    ->setName('experience_years')
    ->setValue(4.5) // годы опыта
    ->setDecimals(1);

echo $number->render() . "<br>";

$span = new SpanText('PHP Backend developer', 'position'); // должность
echo $span->render() . "<br>";

$spanNumber = (new SpanNumber(9))->setDecimals(0);  // уровень навыка
echo $spanNumber->render() . "<br>";


$link = new Link('https://github.com/ivan-ivanov', 'GitHub', 'profile_link'); // ссылка
echo $link->render();

