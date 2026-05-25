<?php
include "conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'] ?? null; // Jenis file (so, sj, bast, invoice)
    $quoId = $_POST['quo_id'] ?? null;

    if (!$type || !$quoId) {
        echo "<script>alert('Invalid input data!'); window.history.back();</script>";
        exit;
    }

    try {
        if ($type === 'so') {
            $noso = $_POST['no_so'] ?? null;
            $tanggalRencana = $_POST['tanggal_perencanaan'] ?? null;
            $alamatInstalasi = $_POST['alamat_instalasi'] ?? null;
            $contactPerson = $_POST['contact_person'] ?? null;

            if (!$noso || !$tanggalRencana || !$alamatInstalasi || !$contactPerson) {
                echo "<script>alert('All fields are required for SO!'); window.history.back();</script>";
                exit;
            }

            $checkQuery = "SELECT id FROM progress WHERE quo_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("i", $quoId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $query = "UPDATE progress 
                          SET so = ?, tanggal_rencana_pengerjaan = ?, alamat_instalasi = ?, contact_person = ? 
                          WHERE quo_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssii", $noso, $tanggalRencana, $alamatInstalasi, $contactPerson, $quoId);
            } else {
                $query = "INSERT INTO progress (quo_id, so, tanggal_rencana_pengerjaan, alamat_instalasi, contact_person) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issss", $quoId, $noso, $tanggalRencana, $alamatInstalasi, $contactPerson);
            }
        } elseif ($type === 'sj') {
            $noSj = $_POST['no_sj'] ?? null;
            $tanggalSj = $_POST['tanggal_sj'] ?? null;

            if (!$noSj || !$tanggalSj) {
                echo "<script>alert('All fields are required for SJ!'); window.history.back();</script>";
                exit;
            }

            $query = "UPDATE progress SET sj = ?, tanggal_sj = ? WHERE quo_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $noSj, $tanggalSj, $quoId);
        } elseif ($type === 'bast') {
            $noBast = $_POST['no_bast'] ?? null;
            $tanggalBast = $_POST['tanggal_bast'] ?? null;
            $keterangan = $_POST['keterangan'] ?? null;

            if (!$noBast || !$tanggalBast || !$keterangan) {
                echo "<script>alert('All fields are required for BAST!'); window.history.back();</script>";
                exit;
            }

            $query = "UPDATE progress SET bast = ?, tanggal_bast = ?, keterangan = ? WHERE quo_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $noBast, $tanggalBast, $keterangan, $quoId);
        } elseif ($type === 'invoice') {
            $noInvoice = $_POST['no_invoice'] ?? null;
            $tanggalInvoice = $_POST['tanggal_invoice'] ?? null;

            if (!$noInvoice || !$tanggalInvoice) {
                echo "<script>alert('All fields are required for Invoice!'); window.history.back();</script>";
                exit;
            }

            $query = "UPDATE progress SET invoice = ?, tanggal_inv = ? WHERE quo_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $noInvoice, $tanggalInvoice, $quoId);
        } else {
            echo "<script>alert('Invalid type!'); window.history.back();</script>";
            exit;
        }

        if ($stmt->execute()) {
            echo "<script>alert('Data saved successfully!'); window.location = 'quoProgress.php';</script>";
        } else {
            echo "<script>alert('Failed to save data!'); window.history.back();</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>
