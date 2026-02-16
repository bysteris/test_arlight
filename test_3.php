<?
$ar = [5, 2, 1024, 0, 512, 2, 5, 100, 0, 1024];

// решение с sort самое дефолтное и на продакшене я бы использовал его наверное практически  во всех случаях
sort($ar);
print_r($ar);

// но как вариант, из задачи мы знаем диапазаон от 0 до 1024 и можем перебрать используя написанную функцию, будет производительнее
function arSort(array $ar): array
{
    $max = 1024;
    
    $counts = array_fill(0, $max + 1, 0);

    foreach ($ar as $number) {
        $counts[$number]++;
    }

    $result = [];

    for ($i = 0; $i <= $max; $i++) {
        while ($counts[$i] > 0) {
            $result[] = $i;
            $counts[$i]--;
        }
    }

    return $result;
}

$sorted = arSort($ar);
print_r($sorted);