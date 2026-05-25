<?php
if ($totalRow < 5) {
    $remaining = 13 - $totalRow;
    for ($i = 0; $i < $remaining; $i++) {
?>
<tr>
    <td class="pt-5"></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<?php
    }
}
else if ($totalRow > 5 && $totalRow < 12) {
    $remaining = 10 - $totalRow;
    for ($i = 0; $i < $remaining; $i++) {
?>
<tr>
    <td class="pt-5"></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<?php
    }
}
?>