<?php
$tambahRow = 22;
if($link == "Y"){
    $tambahRow = 18;
}
if ($totalRow < $tambahRow) {
    $remaining = $tambahRow - $totalRow;
    for ($i = 0; $i < $remaining; $i++) {
?>
<tr>
    <td class="pt-4"></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
    <?php
    }
} 
?>