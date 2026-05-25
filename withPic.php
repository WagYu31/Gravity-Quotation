<div class="col-12 mt-3">
    <div class="row">
        <table class="table-bordered w-100" style="color:black;">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center p-1">NO</th>
                    <th style="width: 46%;" class="text-center p-1">DESCRIPTION</th>
                    <th style="width: 13%;" class="text-center p-1">PICTURE</th>
                    <th style="width: 8%;" class="text-center p-1">QTY</th>
                    <th style="width: 13%;" class="text-center p-1">PRICE</th>
                    <th style="width: 15%;" class="text-center p-1">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center p-1" style="font-size:10px; text-transform:capitalize;"> <?= htmlspecialchars($no); ?> </td>
                                        <td>
                                            <b style="font-size:12px; text-transform:uppercase; margin-bottom:12px;">
                                                <?= htmlspecialchars($row['barang_name'] ?? $row['kategori'] ?? ''); ?>
                                            </b>
                                            <?php if (!empty($row['description'])): ?>
                                                <br>
                                                <span style="font-size:10px; line-height:1.0;">
                                                    <!--<?= htmlspecialchars(strlen($row['description']) > 65 ? substr($row['description'], 0, 65) . '...' : $row['description']); ?>-->
                                                    <?= htmlspecialchars($row['description'] ?? ''); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($link) && $link === "Y"): ?>
                                                <br>
                                                <?php if (!empty($row['link_1'])): ?>
                                                    <span style="font-size:10px;color:blue;">Click here to view ➜ </span>
                                                    <a style="font-size:10px; padding:2px 5px; background-color:#eeefff; border-radius:5px;" href="<?= htmlspecialchars($row['link_1']); ?>">
                                                        <?= htmlspecialchars($row['name_link_1'] ?? 'Link 1'); ?>
                                                    </a>
                                                <?php endif; ?>
                                                &nbsp;&nbsp;
                                                <?php if (!empty($row['link_2'])): ?>
                                                    <a style="font-size:10px; padding:2px 5px; background-color:#eeefff; border-radius:5px;" href="<?= htmlspecialchars($row['link_2']); ?>">
                                                        <?= htmlspecialchars($row['name_link_2'] ?? 'Link 2'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                        <td class="text-center p-1">
                            <?php if (!empty($row['image'])): ?>
                                <img src="uploads/products/<?= htmlspecialchars($row['image']); ?>"
                                    class="img-fluid"
                                    style="max-height:45px; width:auto; object-fit:contain;">
                            <?php endif; ?>
                        </td>
                        <td class="text-center p-1" style="font-size:12px; line-height:1.0; text-transform:uppercase;"> <?= htmlspecialchars($row['qty']); ?> <?= htmlspecialchars($row['satuan']); ?> </td>
                        <td class="text-end px-2" style="font-size:13px; line-height:1.0;">
                                            <span class="float-start">Rp</span>
                                            <span class="float-end"><?= number_format($row['price'], 0); ?></span>
                                        </td>
                                        <td class="text-end px-2" style="font-size:13px; line-height:1.0;">
                                            <span class="float-start">Rp</span>
                                            <span class="float-end"><?= number_format($row['amount'], 0); ?></span>
                                        </td>
                    </tr>
                <?php $no++;
                endwhile; ?>

                <?php include "addRow.php"; ?>

                <?php if (!($total_discount == 0 && (!$disc_all || $disc_all == NULL) && $from !== "CV")): ?>
                    <tr>
                        <td colspan="5" class="text-end" style="font-size:13px; line-height:1.0;"><strong>Sub Total</strong></td>
                        <td class="text-end px-2" style="font-size:13px; line-height:1.0;">
                            <span class="float-start">Rp</span>
                            <span class="float-end"><?= number_format($total_sub_amount, 0, ',', '.'); ?></span>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ($total_discount != 0): ?>
                    <tr>
                        <td colspan="5" class="text-end" style="font-size:13px; line-height:1.0;"><strong>Discount Product</strong></td>
                        <td class="text-end px-2" style="font-size:13px; line-height:1.0;">
                            <span class="float-start">Rp</span>
                            <span class="float-end"><?= number_format($total_discount, 0, ',', '.'); ?></span>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ($disc_all != 0 || $disc_all != NULL): ?>
                    <tr>
                        <td colspan="5" class="text-end" style="font-size:13px; line-height:1.0;"><strong>Special Discount</strong></td>
                        <td class="text-end px-2" style="font-size:13px; line-height:1.0;">
                            <span class="float-start">Rp</span>
                            <span class="float-end"><?= number_format($disc_all, 0, ',', '.'); ?></span>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ($from === 'CV'): ?>
                    <tr>
                        <td colspan="5" class="text-end" style="font-size:13px; line-height:1.0;"><strong>PPN 11%</strong></td>
                        <td class="text-end px-2" style="font-size:13px; line-height:1.0;">
                            <span class="float-start">Rp</span>
                            <span class="float-end"><?= number_format($ppn, 0, ',', '.'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end" style="font-size:13px; line-height:1.0;"><strong>Grand Total</strong></td>
                        <td class="text-end px-2" style="font-size:13px; line-height:1.0; font-weight:bold;">
                            <span class="float-start">Rp</span>
                            <span class="float-end"><?= number_format($total_ppn, 0, ',', '.'); ?></span>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-end" style="font-size:13px; line-height:1.0;"><strong>Grand Total</strong></td>
                        <td class="text-end px-2" style="font-size:13px; line-height:1.0; font-weight:bold;">
                            <span class="float-start">Rp</span>
                            <span class="float-end"><?= number_format($total_non_ppn, 0, ',', '.'); ?></span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>