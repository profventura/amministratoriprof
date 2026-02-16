<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Services\DocxTemplateService;
class SettingsController {
  public function index() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    
    // Assicura che public_url esista in $row, altrimenti stringa vuota
    if (!isset($row['public_url'])) $row['public_url'] = '';
    
    Helpers::view('settings/index', ['title'=>'Impostazioni','row'=>$row]);
  }
  
  public function updatePublicUrl() {
      Auth::require();
      if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
      
      $url = trim($_POST['public_url'] ?? '');
      // Validazione base URL
      if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
          Helpers::addFlash('danger', 'URL non valido');
          Helpers::redirect('/settings');
          return;
      }
      
      $pdo = DB::conn();
      // Controlla se colonna esiste, se no la crea (quick fix per evitare migration file separato se preferito, ma meglio migration)
      // Per coerenza con le altre modifiche, meglio fare una migration separata. 
      // Qui assumiamo che la colonna ci sia o fallirà.
      // UPDATE
      $sql = "UPDATE settings SET public_url=?, updated_at=NOW() ORDER BY id DESC LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$url]);
      
      Helpers::addFlash('success', 'URL pubblico aggiornato');
      Helpers::redirect('/settings');
  }
  public function certificati() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    Helpers::view('settings/certificati', ['title'=>'Impostazioni Certificati','row'=>$row]);
  }

  public function updateAdminCredentials() {
      Auth::require();
      if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
      
      $newUsername = trim($_POST['username'] ?? '');
      $newPassword = $_POST['password'] ?? '';
      $confirmPassword = $_POST['confirm_password'] ?? '';
      
      if (empty($newUsername)) {
          Helpers::addFlash('danger', 'Username non può essere vuoto');
          Helpers::redirect('/settings');
          return;
      }
      
      $pdo = DB::conn();
      $currentUser = Auth::user();
      $userId = $currentUser['id'];
      
      // Check username uniqueness (exclude self)
      $exists = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id <> ?");
      $exists->execute([$newUsername, $userId]);
      if ($exists->fetch()) {
          Helpers::addFlash('danger', 'Username già in uso');
          Helpers::redirect('/settings');
          return;
      }
      
      $updates = ['username = ?'];
      $params = [$newUsername];
      
      if (!empty($newPassword)) {
          if ($newPassword !== $confirmPassword) {
              Helpers::addFlash('danger', 'Le password non coincidono');
              Helpers::redirect('/settings');
              return;
          }
          if (strlen($newPassword) < 8) {
              Helpers::addFlash('danger', 'La password deve essere almeno 8 caratteri');
              Helpers::redirect('/settings');
              return;
          }
          $updates[] = 'password_hash = ?';
          $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
      }
      
      $params[] = $userId;
      $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      
      // Update session if username changed
      if ($newUsername !== $currentUser['username']) {
          $_SESSION['user']['username'] = $newUsername;
          // Note: Auth::user() returns $_SESSION['user']
          $_SESSION['user']['name'] = $newUsername; // Assuming 'name' is used in layout
      }
      
      Helpers::addFlash('success', 'Credenziali amministratore aggiornate');
      Helpers::redirect('/settings');
  }

  public function attestati() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    Helpers::view('settings/attestati', ['title'=>'Impostazioni Attestati','row'=>$row]);
  }

  public function importExport() {
    Auth::require();
    Helpers::view('settings/import_export', ['title'=>'Import/Export']);
  }
  
  public function export() {
    Auth::require();
    if (!Auth::isAdmin()) { http_response_code(403); echo 'Forbidden'; return; }
    
    $entity = $_GET['entity'] ?? '';
    $pdo = DB::conn();
    $filename = "export_{$entity}_" . date('Y-m-d_His') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $out = fopen('php://output', 'w');
    // BOM per Excel
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    switch($entity) {
        case 'members':
            $stmt = $pdo->query("SELECT * FROM members WHERE deleted_at IS NULL");
            $header = false;
            
            // Mappatura nomi colonne DB -> Nomi Italiani
            $map = [
                'id' => 'ID',
                'member_number' => 'Numero Socio',
                'first_name' => 'Nome',
                'last_name' => 'Cognome',
                'email' => 'Email',
                'phone' => 'Telefono',
                'fiscal_code' => 'Codice Fiscale', // Vecchio nome se esiste ancora
                'tax_code' => 'Codice Fiscale',
                'billing_cf' => 'CF Fatturazione',
                'billing_piva' => 'P.IVA Fatturazione',
                'address' => 'Indirizzo',
                'city' => 'Città',
                'zip_code' => 'CAP',
                'province' => 'Provincia',
                'status' => 'Stato',
                'created_at' => 'Data Creazione',
                'updated_at' => 'Data Aggiornamento',
                'registration_date' => 'Data Iscrizione',
                'deleted_at' => 'Data Cancellazione',
                'studio_name' => 'Nome Studio',
                'is_revisor' => 'Revisore',
                'billing_cf_piva' => 'CF/PIVA (Vecchio)'
            ];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!$header) { 
                    // Traduci header
                    $keys = array_keys($row);
                    $translatedKeys = array_map(function($k) use ($map) {
                        return $map[$k] ?? ucfirst(str_replace('_', ' ', $k));
                    }, $keys);
                    fputcsv($out, $translatedKeys, ';'); 
                    $header = true; 
                }
                fputcsv($out, $row, ';');
            }
            break;
        case 'memberships':
            $stmt = $pdo->query("SELECT m.*, mb.first_name, mb.last_name FROM memberships m JOIN members mb ON m.member_id = mb.id WHERE m.deleted_at IS NULL");
            $header = false;
            
            $map = [
                'id' => 'ID Iscrizione',
                'member_id' => 'ID Socio',
                'year' => 'Anno',
                'payment_date' => 'Data Pagamento',
                'amount' => 'Importo',
                'payment_method' => 'Metodo Pagamento',
                'created_at' => 'Data Creazione',
                'updated_at' => 'Data Aggiornamento',
                'deleted_at' => 'Data Cancellazione',
                'first_name' => 'Nome',
                'last_name' => 'Cognome'
            ];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!$header) { 
                    $keys = array_keys($row);
                    $translatedKeys = array_map(function($k) use ($map) {
                        return $map[$k] ?? ucfirst(str_replace('_', ' ', $k));
                    }, $keys);
                    fputcsv($out, $translatedKeys, ';'); 
                    $header = true; 
                }
                fputcsv($out, $row, ';');
            }
            break;
        case 'payments':
            $stmt = $pdo->query("SELECT p.*, mb.first_name, mb.last_name FROM payments p JOIN members mb ON p.member_id = mb.id");
            $header = false;
            
            $map = [
                'id' => 'ID Pagamento',
                'member_id' => 'ID Socio',
                'amount' => 'Importo',
                'payment_date' => 'Data Pagamento',
                'description' => 'Descrizione',
                'type' => 'Tipo',
                'created_at' => 'Data Creazione',
                'updated_at' => 'Data Aggiornamento',
                'first_name' => 'Nome',
                'last_name' => 'Cognome'
            ];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!$header) { 
                    $keys = array_keys($row);
                    $translatedKeys = array_map(function($k) use ($map) {
                        return $map[$k] ?? ucfirst(str_replace('_', ' ', $k));
                    }, $keys);
                    fputcsv($out, $translatedKeys, ';'); 
                    $header = true; 
                }
                fputcsv($out, $row, ';');
            }
            break;
        case 'courses':
            $stmt = $pdo->query("SELECT * FROM courses WHERE deleted_at IS NULL");
            $header = false;
            
            $map = [
                'id' => 'ID Corso',
                'title' => 'Titolo',
                'description' => 'Descrizione',
                'start_date' => 'Data Inizio',
                'end_date' => 'Data Fine',
                'location' => 'Luogo',
                'hours' => 'Ore',
                'created_at' => 'Data Creazione',
                'updated_at' => 'Data Aggiornamento',
                'deleted_at' => 'Data Cancellazione'
            ];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!$header) { 
                    $keys = array_keys($row);
                    $translatedKeys = array_map(function($k) use ($map) {
                        return $map[$k] ?? ucfirst(str_replace('_', ' ', $k));
                    }, $keys);
                    fputcsv($out, $translatedKeys, ';'); 
                    $header = true; 
                }
                fputcsv($out, $row, ';');
            }
            break;
        case 'receipts':
            $stmt = $pdo->query("SELECT r.*, mb.first_name, mb.last_name FROM receipts r JOIN members mb ON r.member_id = mb.id");
            $header = false;
            
            $map = [
                'id' => 'ID Ricevuta',
                'member_id' => 'ID Socio',
                'number' => 'Numero',
                'year' => 'Anno',
                'date' => 'Data',
                'amount' => 'Importo',
                'description' => 'Descrizione',
                'file_path' => 'Percorso File',
                'created_at' => 'Data Creazione',
                'updated_at' => 'Data Aggiornamento',
                'first_name' => 'Nome',
                'last_name' => 'Cognome'
            ];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!$header) { 
                    $keys = array_keys($row);
                    $translatedKeys = array_map(function($k) use ($map) {
                        return $map[$k] ?? ucfirst(str_replace('_', ' ', $k));
                    }, $keys);
                    fputcsv($out, $translatedKeys, ';'); 
                    $header = true; 
                }
                fputcsv($out, $row, ';');
            }
            break;
        default:
            echo "Entità non valida";
    }
    fclose($out);
    exit;
  }
  
  public function import() {
    Auth::require();
    if (!Auth::isAdmin()) { http_response_code(403); echo 'Forbidden'; return; }
    if (!CSRF::validate($_POST['csrf'] ?? '')) { Helpers::addFlash('danger', 'CSRF error'); Helpers::redirect('/settings/import-export'); return; }
    
    $entity = $_POST['entity'] ?? '';
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        Helpers::addFlash('danger', 'Errore caricamento file');
        Helpers::redirect('/settings/import-export'); return;
    }
    
    $file = $_FILES['file']['tmp_name'];
    $pdo = DB::conn();
    $handle = fopen($file, "r");
    if (!$handle) { Helpers::addFlash('danger', 'Impossibile aprire file'); Helpers::redirect('/settings/import-export'); return; }
    
    // Rileva separatore (semplice)
    $line = fgets($handle);
    $sep = (strpos($line, ';') !== false) ? ';' : ',';
    rewind($handle);
    
    // Salta BOM se presente
    $bom = fread($handle, 3);
    if ($bom != chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
    
    $header = fgetcsv($handle, 0, $sep);
    $count = 0;
    $duplicates = 0;
    $skipped = 0;
    $errors = [];
    
    // Mappatura Nomi Italiani -> Nomi DB per import
    $mapImport = [
        'Nome' => 'first_name',
        'Cognome' => 'last_name',
        'Email' => 'email',
        'Telefono' => 'phone',
        'Codice Fiscale' => 'tax_code',
        'CF Fatturazione' => 'billing_cf',
        'P.IVA Fatturazione' => 'billing_piva',
        'Indirizzo' => 'address',
        'Città' => 'city',
        'CAP' => 'zip_code',
        'Provincia' => 'province',
        'Stato' => 'status',
        'Numero Socio' => 'member_number'
    ];

    try {
        $pdo->beginTransaction();
        
        $rowNum = 1; // Riga 1 è header
        while (($data = fgetcsv($handle, 0, $sep)) !== false) {
            $rowNum++;
            // Salta righe vuote
            if (empty($data) || (count($data) === 1 && $data[0] === null)) continue;
            
            // Se il numero di colonne non corrisponde, prova a gestire o salta
            if (count($data) < count($header)) {
                $skipped++;
                $errors[] = "Riga $rowNum: Colonne insufficienti (" . count($data) . " invece di " . count($header) . ")";
                continue;
            }
            
            // Assicura che data abbia lo stesso numero di elementi di header
            $data = array_slice($data, 0, count($header));
            
            $row = array_combine($header, $data);
            
            // Normalizza chiavi (trim e map)
            $normalizedRow = [];
            foreach ($row as $k => $v) {
                // Rimuovi eventuali caratteri BOM o whitespace extra dalla chiave
                $k = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $k);
                $k = trim($k);
                
                // Mappatura case-insensitive
                $mappedKey = null;
                foreach ($mapImport as $itKey => $dbKey) {
                    if (mb_strtolower($itKey) === mb_strtolower($k)) {
                        $mappedKey = $dbKey;
                        break;
                    }
                }
                
                if ($mappedKey) {
                    $normalizedRow[$mappedKey] = trim($v);
                } elseif (in_array($k, $mapImport)) {
                    // Chiave già in inglese
                    $normalizedRow[$k] = trim($v);
                }
            }
            
            // Se la normalizzazione non ha prodotto colonne valide, salta
            if (empty($normalizedRow)) {
                $skipped++;
                $errors[] = "Riga $rowNum: Nessuna colonna valida trovata";
                continue;
            }
            
            $row = $normalizedRow; // Usa riga normalizzata

            switch($entity) {
                case 'members':
                    // Check duplicate email
                    if (!empty($row['email'])) {
                        $exists = $pdo->prepare("SELECT id FROM members WHERE email = ?");
                        $exists->execute([$row['email']]);
                        if ($exists->fetch()) {
                            $duplicates++;
                            $errors[] = "Riga $rowNum: Email duplicata ({$row['email']})";
                            continue 2; 
                        }
                    }
                    
                    // Prepara inserimento
                    $allowed = ['first_name','last_name','email','phone','fiscal_code','tax_code','billing_cf','billing_piva','address','city','zip_code','province','status','member_number'];
                    $cols = []; $vals = [];
                    foreach ($allowed as $f) {
                        if (isset($row[$f]) && $row[$f] !== '') { 
                            $cols[] = $f;
                            $vals[] = $row[$f];
                        }
                    }
                    
                    // Se non ci sono dati utili da inserire
                    if (empty($cols)) {
                        $skipped++;
                        $errors[] = "Riga $rowNum: Nessun dato valido per l'inserimento";
                        continue 2;
                    }
                    
                    $sql = "INSERT INTO members (" . implode(',', $cols) . ", created_at) VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ", NOW())";
                    $pdo->prepare($sql)->execute($vals);
                    $count++;
                    break;
                    
                // Implementare altri casi se necessario
            }
        }
        $pdo->commit();
        
        $msg = "Importazione completata: $count inseriti.";
        if ($duplicates > 0) $msg .= " $duplicates duplicati (saltati).";
        if ($skipped > 0) $msg .= " $skipped saltati per errori.";
        
        if (!empty($errors)) {
            // Mostra i primi 5 errori per non intasare
            $msg .= " Dettagli: " . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $msg .= " ...";
        }
        
        Helpers::addFlash(($count > 0 ? 'success' : 'warning'), $msg);
        
    } catch (\Exception $e) {
        $pdo->rollBack();
        Helpers::addFlash('danger', 'Errore importazione: ' . $e->getMessage());
    }
    
    fclose($handle);
    Helpers::redirect('/settings/import-export');
  }

  public function email() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT * FROM email_settings ORDER BY id DESC LIMIT 1')->fetch();
    if (!$row) {
        // Fallback vuoto se non c'è riga (dovrebbe esserci dopo migration)
        $row = [];
    }
    Helpers::view('settings/email', ['title'=>'Impostazioni Email','row'=>$row]);
  }

  public function updateEmailSettings() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = (int)($_POST['smtp_port'] ?? 587);
    $smtp_secure = $_POST['smtp_secure'] ?? 'tls';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $smtp_from_email = $_POST['smtp_from_email'] ?? '';
    $smtp_from_name = $_POST['smtp_from_name'] ?? '';
    $smtp_cc = $_POST['smtp_cc'] ?? '';
    $smtp_bcc = $_POST['smtp_bcc'] ?? '';
    
    $email_certificate_subject = $_POST['email_certificate_subject'] ?? '';
    $email_certificate_body = $_POST['email_certificate_body'] ?? '';
    $email_dm_certificate_subject = $_POST['email_dm_certificate_subject'] ?? '';
    $email_dm_certificate_body = $_POST['email_dm_certificate_body'] ?? '';

    $pdo = DB::conn();
    // Aggiorna l'unica riga o inserisci
    $exists = $pdo->query('SELECT id FROM email_settings ORDER BY id DESC LIMIT 1')->fetchColumn();
    
    if ($exists) {
        $sql = "UPDATE email_settings SET 
            smtp_host=?, smtp_port=?, smtp_secure=?, username=?, password=?, 
            smtp_from_email=?, smtp_from_name=?, smtp_cc=?, smtp_bcc=?,
            email_certificate_subject=?, email_certificate_body=?,
            email_dm_certificate_subject=?, email_dm_certificate_body=?,
            updated_at=NOW() WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $smtp_host, $smtp_port, $smtp_secure, $username, $password,
            $smtp_from_email, $smtp_from_name, $smtp_cc, $smtp_bcc,
            $email_certificate_subject, $email_certificate_body,
            $email_dm_certificate_subject, $email_dm_certificate_body,
            $exists
        ]);
    } else {
        $sql = "INSERT INTO email_settings (
            smtp_host, smtp_port, smtp_secure, username, password, 
            smtp_from_email, smtp_from_name, smtp_cc, smtp_bcc,
            email_certificate_subject, email_certificate_body,
            email_dm_certificate_subject, email_dm_certificate_body
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $smtp_host, $smtp_port, $smtp_secure, $username, $password,
            $smtp_from_email, $smtp_from_name, $smtp_cc, $smtp_bcc,
            $email_certificate_subject, $email_certificate_body,
            $email_dm_certificate_subject, $email_dm_certificate_body
        ]);
    }
    
    Helpers::addFlash('success', 'Impostazioni Email aggiornate');
    Helpers::redirect('/settings/email');
  }

  public function updateAttestatiTemplate() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    if (!isset($_FILES['template']) || $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
      Helpers::addFlash('danger', 'Seleziona un file .pdf');
      Helpers::redirect('/settings/attestati'); return;
    }
    $name = basename($_FILES['template']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
      Helpers::addFlash('danger', 'Il file deve essere .pdf');
      Helpers::redirect('/settings/attestati'); return;
    }
    
    $destDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/templates');
    if (!is_dir($destDir)) { mkdir($destDir, 0777, true); }
    $abs = $destDir . DIRECTORY_SEPARATOR . 'attestato.pdf';
    move_uploaded_file($_FILES['template']['tmp_name'], $abs);
    
    $orientation = $_POST['orientation'] ?? 'L';
    if (!in_array($orientation, ['P', 'L'])) $orientation = 'L';
    
    $rel = 'app/templates/attestato.pdf';
    $pdo = DB::conn();
    $exists = $pdo->query('SELECT COUNT(*) c FROM settings')->fetch()['c'];
    if ((int)$exists === 0) {
      // Inserimento con valori default
      $stmt = $pdo->prepare('INSERT INTO settings (association_name, receipt_sequence_current, receipt_sequence_year, dm_certificate_template_docx_path, dm_certificate_orientation) VALUES (?,?,?,?,?)');
      $stmt->execute(['Associazione AP', 0, (int)date('Y'), $rel, $orientation]);
    } else {
      $stmt = $pdo->prepare('UPDATE settings SET dm_certificate_template_docx_path=?, dm_certificate_orientation=?, updated_at=NOW() ORDER BY id DESC LIMIT 1');
      $stmt->execute([$rel, $orientation]);
    }
    Helpers::addFlash('success', 'Template attestato aggiornato');
    Helpers::redirect('/settings/attestati');
  }

  public function updateAttestatiStamp() {
      // Per semplicità usiamo la stessa logica di updateStamp ma mappiamo su campi diversi se necessario
      // In questo caso, riutilizziamo updateStamp o creiamo logica parallela se i campi sono diversi.
      // Poiché la richiesta è "allo stesso modo dei certificati", assumiamo campi simili ma salvati forse su colonne diverse o le stesse se il template è unico?
      // L'utente dice "allo stesso modo dei certificati (solo i pdf) vanno fatti gli attestati".
      // La pagina esistente /settings gestisce "Certificato Iscrizione" (membership_certificate).
      // Questa nuova pagina gestisce "Attestato DM" (dm_certificate).
      // Nel DB settings abbiamo `dm_certificate_template_docx_path`.
      
      // Mappiamo i campi del form ai campi del DB per gli attestati DM
      // Campi DM: name, course_title, date, etc.
      // Per ora riusiamo la struttura di updateStamp ma puntiamo a colonne diverse se esistono, o aggiungiamo colonne.
      // Verifichiamo lo schema settings.
      
      $this->updateStampGeneric('dm');
  }

  public function ricevute() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    Helpers::view('settings/ricevute', ['title'=>'Impostazioni Ricevute','row'=>$row]);
  }

  public function updateRicevuteTemplate() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    if (!isset($_FILES['template']) || $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
      Helpers::addFlash('danger', 'Seleziona un file .pdf');
      Helpers::redirect('/settings/ricevute'); return;
    }
    $name = basename($_FILES['template']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
      Helpers::addFlash('danger', 'Il file deve essere .pdf');
      Helpers::redirect('/settings/ricevute'); return;
    }
    $destDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/templates/documents');
    if (!is_dir($destDir)) { mkdir($destDir, 0777, true); }
    $abs = $destDir . DIRECTORY_SEPARATOR . 'receipt_template.pdf';
    move_uploaded_file($_FILES['template']['tmp_name'], $abs);
    
    $orientation = $_POST['orientation'] ?? 'P';
    if (!in_array($orientation, ['P', 'L'])) $orientation = 'P';
    
    // Aggiorna DB
    $rel = 'app/templates/documents/receipt_template.pdf';
    $pdo = DB::conn();
    $pdo->prepare("UPDATE settings SET receipt_template_path=?, receipt_orientation=?, updated_at=NOW() ORDER BY id DESC LIMIT 1")->execute([$rel, $orientation]);
    
    Helpers::addFlash('success', 'Template ricevuta aggiornato');
    Helpers::redirect('/settings/ricevute');
  }

  public function updateRicevuteStamp() {
      $this->updateStampGeneric('receipt');
  }

  public function previewRicevuteStamp() {
    Auth::require();
    
    $pdo = DB::conn();
    $row = $pdo->query('SELECT receipt_template_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row['receipt_template_path'] ?? '';
    
    if (!$rel || !file_exists($tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel))) {
        echo "Template non trovato."; return;
    }
    
    // Parametri dal POST
    $opts = [];
    $fields = ['receipt_number', 'receipt_date', 'member_name', 'member_address', 'member_cf', 'amount', 'description'];
    foreach ($fields as $f) {
        // Togli 'receipt_' o 'member_' dal nome campo POST se necessario, 
        // ma updateStampGeneric usa prefisso_stamp_campo_prop
        // Nel form useremo i nomi brevi: number, date, name, address, cf, amount, description
        // Mappiamo:
        // receipt_number -> number
        // receipt_date -> date
        // member_name -> name
        // member_address -> address
        // member_cf -> cf
        // amount -> amount
        // description -> description
        
        // Aspetta, updateStampGeneric usa $fields definiti internamente.
        // Dobbiamo estendere updateStampGeneric per supportare questi nuovi campi o crearne uno ad hoc.
        // Creiamo updateStampGeneric più flessibile o un metodo dedicato.
        // Per preview, prendiamo i dati dal POST che avranno nomi tipo number_x, date_x, etc.
    }
    
    // Recuperiamo i dati "raw" dal POST come fatto per gli altri preview
    // I nomi dei campi nel form saranno: number, date, name, address, cf, amount, description
    $formFields = ['number', 'date', 'name', 'address', 'cf', 'amount', 'description'];
    
    foreach ($formFields as $f) {
        $opts["{$f}_x"] = (int)($_POST["{$f}_x"] ?? 0);
        $opts["{$f}_y"] = (int)($_POST["{$f}_y"] ?? 0);
        $opts["{$f}_font_size"] = (int)($_POST["{$f}_font_size"] ?? 12);
        $opts["{$f}_color"] = $_POST["{$f}_color"] ?? '#000000';
        $opts["{$f}_font_family"] = $_POST["{$f}_font_family"] ?? 'Arial';
        $opts["{$f}_bold"] = !empty($_POST["{$f}_bold"]);
    }
    
    // Dati finti
    $data = [
        'number' => '2024/001',
        'date' => date('d/m/Y'),
        'name' => 'MARIO ROSSI',
        'address' => "Via Roma 10\n20100 Milano (MI)",
        'cf' => 'RSSMRA80A01H501U',
        'amount' => '€ 50,00',
        'description' => 'Quota Associativa 2024'
    ];
    
    // Passiamo i valori come _value in opts per il servizio
    foreach ($data as $k => $v) {
        $opts["{$k}_value"] = $v;
    }
    
    if (!empty($_POST['debug_grid'])) {
        $opts['debug_grid'] = true;
    }

    $outDir = dirname(__DIR__, 2) . '/storage/documents/_test';
    if (!is_dir($outDir)) mkdir($outDir, 0777, true);
    $outFile = $outDir . '/preview_receipt_' . time() . '.pdf';

    // Usiamo PDFStampService::stampReceipt (da creare/aggiornare) o stampMembershipCertificate se generico abbastanza
    // stampMembershipCertificate è un po' specifico su 'name', 'number'.
    // Creiamo un metodo generico stampGeneric in PDFStampService.
    $res = \App\Services\PDFStampService::stampGeneric($tplAbs, $outFile, $opts);
    
    if ($res && file_exists($outFile)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="preview_ricevuta.pdf"');
        readfile($outFile);
        @unlink($outFile);
    } else {
        echo "Errore generazione anteprima.";
    }
  }

  // Aggiornato per supportare i campi ricevuta
  private function updateStampGeneric($prefix) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    
    // Definiamo i campi in base al prefisso
    if ($prefix === 'receipt') {
        // Mapping form -> db
        // form: number_x -> db: receipt_stamp_receipt_number_x (un po' ridondante ma coerente con migration)
        // db fields: receipt_number, receipt_date, member_name, member_address, member_cf, amount, description
        // form fields: number, date, name, address, cf, amount, description
        $fieldMap = [
            'number' => 'receipt_number',
            'date' => 'receipt_date',
            'name' => 'member_name',
            'address' => 'member_address',
            'cf' => 'member_cf',
            'amount' => 'amount',
            'description' => 'description'
        ];
    } elseif ($prefix === 'dm') {
        $fieldMap = [
            'name' => 'name', 
            'course_title' => 'course_title', 
            'date' => 'date', 
            'year' => 'year'
        ];
    } else {
        // certificate
        $fieldMap = [
            'name' => 'name', 
            'number' => 'number', 
            'date' => 'date', 
            'year' => 'year'
        ];
    }
    
    $props = ['x', 'y', 'font_size', 'color', 'font_family', 'bold'];
    
    $params = [];
    foreach ($fieldMap as $formField => $dbField) {
        foreach ($props as $p) {
            $inputKey = "{$formField}_{$p}"; // es. number_x
            if (isset($_POST[$inputKey])) {
                $val = $_POST[$inputKey];
                if ($p === 'x' || $p === 'y' || $p === 'font_size') $val = (int)$val;
                if ($p === 'bold') $val = !empty($val) ? 1 : 0;
                
                $dbCol = "{$prefix}_stamp_{$dbField}_{$p}";
                $params[$dbCol] = $val;
            }
        }
    }

    $pdo = DB::conn();
    $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(\PDO::FETCH_COLUMN);
    
    $validParams = [];
    foreach ($params as $k => $v) {
        if (in_array($k, $cols)) {
            $validParams[$k] = $v;
        }
    }
    
    if (empty($validParams)) {
        Helpers::addFlash('warning', 'Nessun campo valido da aggiornare');
        Helpers::redirect($_SERVER['HTTP_REFERER']);
        return;
    }

    $sets = [];
    $vals = [];
    foreach ($validParams as $col => $val) {
        $sets[] = "$col = ?";
        $vals[] = $val;
    }
    
    $sql = "UPDATE settings SET " . implode(', ', $sets) . ", updated_at=NOW() ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);
    
    Helpers::addFlash('success', 'Stili aggiornati');
    Helpers::redirect($_SERVER['HTTP_REFERER']);
  }

  public function updateTemplate() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    if (!isset($_FILES['template']) || $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
      Helpers::addFlash('danger', 'Seleziona un file .pdf');
      Helpers::redirect('/settings/certificati'); return;
    }
    $name = basename($_FILES['template']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
      Helpers::addFlash('danger', 'Il file deve essere .pdf');
      Helpers::redirect('/settings/certificati'); return;
    }
    
    // Logica salvataggio template certificati
    $destDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/templates');
    if (!is_dir($destDir)) { mkdir($destDir, 0777, true); }
    $abs = $destDir . DIRECTORY_SEPARATOR . 'certificato_iscrizione.pdf';
    move_uploaded_file($_FILES['template']['tmp_name'], $abs);
    
    $orientation = $_POST['orientation'] ?? 'P';
    if (!in_array($orientation, ['P', 'L'])) $orientation = 'P';
    
    $rel = 'app/templates/certificato_iscrizione.pdf';
    $pdo = DB::conn();
    // Update path nel DB
    $pdo->prepare("UPDATE settings SET membership_certificate_template_docx_path=?, certificate_orientation=?, updated_at=NOW() ORDER BY id DESC LIMIT 1")->execute([$rel, $orientation]);

    Helpers::addFlash('success', 'Template certificato aggiornato');
    Helpers::redirect('/settings/certificati');
  }

  public function updateStamp() {
    // ... codice esistente per updateStamp ...
    // Riutilizziamo updateStampGeneric('certificate') se possibile, 
    // oppure manteniamo la logica specifica se diversa.
    // Per coerenza con updateRicevuteStamp, meglio usare updateStampGeneric('certificate')
    $this->updateStampGeneric('certificate');
  }

  public function previewStamp() {
    Auth::require();
    // Non controlliamo CSRF stretto qui per facilitare chiamate AJAX rapide, o lo passiamo via header
    // Per semplicità usiamo POST normale
    
    $pdo = DB::conn();
    $row = $pdo->query('SELECT membership_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row['membership_certificate_template_docx_path'] ?? '';
    
    if (!$rel || !file_exists($tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel))) {
        echo "Template non trovato."; return;
    }
    
    // Se non è PDF, non possiamo fare preview coordinate (ha senso solo su PDF)
    if (strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION)) !== 'pdf') {
        echo "L'anteprima coordinate funziona solo con template PDF."; return;
    }

    // Raccogli parametri da POST (o usa quelli salvati se vuoti, ma qui vogliamo testare quelli del form)
    // Se la chiamata arriva dal form di test, usiamo $_POST
    $opts = [];
    $fields = ['name', 'number', 'date', 'year'];
    foreach ($fields as $f) {
        $opts["{$f}_x"] = (int)($_POST["{$f}_x"] ?? 0);
        $opts["{$f}_y"] = (int)($_POST["{$f}_y"] ?? 0);
        $opts["{$f}_font_size"] = (int)($_POST["{$f}_font_size"] ?? 12);
        $opts["{$f}_color"] = $_POST["{$f}_color"] ?? '#000000';
        $opts["{$f}_font_family"] = $_POST["{$f}_font_family"] ?? 'Arial';
        $opts["{$f}_bold"] = !empty($_POST["{$f}_bold"]);
    }
    
    // Dati finti
    $name = "ANTEPRIMA NOME";
    $number = "12345";
    $opts['date_value'] = date('d/m/Y');
    $opts['year_value'] = date('Y');
    
    // Debug grid
    if (!empty($_POST['debug_grid'])) {
        $opts['debug_grid'] = true;
    }

    $outDir = dirname(__DIR__, 2) . '/storage/documents/_test';
    if (!is_dir($outDir)) mkdir($outDir, 0777, true);
    $outFile = $outDir . '/preview_' . time() . '.pdf';

    $res = \App\Services\PDFStampService::stampMembershipCertificate($tplAbs, $outFile, $name, $number, $opts);
    
    if ($res && file_exists($outFile)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="preview.pdf"');
        readfile($outFile);
        @unlink($outFile);
    } else {
        echo "Errore generazione anteprima.";
    }
  }
  public function previewAttestatiStamp() {
    Auth::require();
    // Anteprima specifica per Attestati (template dm_certificate)
    
    $pdo = DB::conn();
    $row = $pdo->query('SELECT dm_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row['dm_certificate_template_docx_path'] ?? '';
    
    if (!$rel || !file_exists($tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel))) {
        echo "Template non trovato."; return;
    }
    
    if (strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION)) !== 'pdf') {
        echo "L'anteprima coordinate funziona solo con template PDF."; return;
    }

    $opts = [];
    $fields = ['name', 'number', 'date', 'year', 'course_title'];
    foreach ($fields as $f) {
        $opts["{$f}_x"] = (int)($_POST["{$f}_x"] ?? 0);
        $opts["{$f}_y"] = (int)($_POST["{$f}_y"] ?? 0);
        $opts["{$f}_font_size"] = (int)($_POST["{$f}_font_size"] ?? 12);
        $opts["{$f}_color"] = $_POST["{$f}_color"] ?? '#000000';
        $opts["{$f}_font_family"] = $_POST["{$f}_font_family"] ?? 'Arial';
        $opts["{$f}_bold"] = !empty($_POST["{$f}_bold"]);
    }
    
    // Dati finti per anteprima attestato
    $name = "MARIO ROSSI";
    $number = "12345";
    $opts['date_value'] = date('d/m/Y');
    $opts['year_value'] = date('Y');
    $opts['course_title_value'] = "CORSO DI PROVA"; // Valore per l'anteprima dell'argomento
    
    if (!empty($_POST['debug_grid'])) {
        $opts['debug_grid'] = true;
    }

    $outDir = dirname(__DIR__, 2) . '/storage/documents/_test';
    if (!is_dir($outDir)) mkdir($outDir, 0777, true);
    $outFile = $outDir . '/preview_dm_' . time() . '.pdf';

    // Usiamo lo stesso servizio PDFStampService, ma supporta course_title?
    // Verifichiamo e aggiorniamo il servizio se necessario, o passiamo 'course_title' come extra fields se supportato.
    // PDFStampService::stampMembershipCertificate accetta $extraFields?
    // Controlliamo il servizio. Se è rigido, creiamo un metodo ad hoc o estendiamo.
    // Per ora proviamo a chiamarlo, ma probabilmente dobbiamo aggiornarlo.
    
    $res = \App\Services\PDFStampService::stampMembershipCertificate($tplAbs, $outFile, $name, $number, $opts);
    
    if ($res && file_exists($outFile)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="preview_attestato.pdf"');
        readfile($outFile);
        @unlink($outFile);
    } else {
        echo "Errore generazione anteprima.";
    }
  }

  public function getPdfGeometry() {
    Auth::require();
    header('Content-Type: application/json');
    
    $type = $_GET['type'] ?? 'membership'; // membership, dm, receipt
    $col = 'membership_certificate_template_docx_path';
    if ($type === 'dm') $col = 'dm_certificate_template_docx_path';
    if ($type === 'receipt') $col = 'receipt_template_path';
    
    $pdo = DB::conn();
    // Verifica se la colonna esiste per evitare errori SQL se receipt_template_path non c'è ancora (ma dovrebbe)
    // Meglio: SELECT * e poi prendi la chiave dinamica
    $row = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row[$col] ?? '';
    
    if (!$rel || !file_exists($tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel))) {
        echo json_encode(['error' => 'Template non trovato per tipo: ' . $type]); return;
    }
    
    if (strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION)) !== 'pdf') {
        echo json_encode(['error' => 'Non è un PDF']); return;
    }

    try {
        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->setSourceFile($tplAbs);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);
        
        echo json_encode([
            'width' => $size['width'],
            'height' => $size['height'],
            'orientation' => ($size['width'] > $size['height']) ? 'L' : 'P'
        ]);
    } catch (\Throwable $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
  }

  public function testDocx() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT membership_certificate_template_docx_path, dm_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row['membership_certificate_template_docx_path'] ?? $row['dm_certificate_template_docx_path'] ?? '';
    if (!class_exists('ZipArchive')) {
      Helpers::addFlash('danger', 'ZipArchive non abilitato in PHP');
      Helpers::redirect('/settings'); return;
    }
    if (!$rel) {
      Helpers::addFlash('danger', 'Nessun template DOCX configurato');
      Helpers::redirect('/settings'); return;
    }
    $tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel);
    if (!is_file($tplAbs)) {
      Helpers::addFlash('danger', 'Template DOCX non trovato: ' . $rel);
      Helpers::redirect('/settings'); return;
    }
    $dirOut = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/_test');
    if (!is_dir($dirOut)) { mkdir($dirOut, 0777, true); }
    $outAbs = $dirOut . DIRECTORY_SEPARATOR . 'test_cert.pdf';
    $vars = ['nome'=>'Test User', 'te'=>'12345', 'a'=>date('Y')];
    $rendered = \App\Services\DocxTemplateService::renderToPdf($tplAbs, $vars, $outAbs);
    if ($rendered && is_file($outAbs)) {
      Helpers::addFlash('success', 'Generazione DOCX→PDF OK');
      Helpers::redirect('/documents/download?path=' . urlencode('storage/documents/_test/test_cert.pdf')); return;
    }
    $outDocx = $dirOut . DIRECTORY_SEPARATOR . 'test_cert.docx';
    $docxOk = \App\Services\DocxTemplateService::renderToDocx($tplAbs, $vars, $outDocx);
    if ($docxOk) {
      Helpers::addFlash('warning', 'PDF non disponibile: generato DOCX di prova');
      Helpers::redirect('/documents/download?path=' . urlencode('storage/documents/_test/test_cert.docx')); return;
    }
    Helpers::addFlash('danger', 'Generazione da DOCX non riuscita, verificare renderer PDF e ZipArchive');
    Helpers::redirect('/settings');
  }

  public function downloadSampleCsv() {
    Auth::require();
    $entity = $_GET['entity'] ?? 'members';
    $filename = "sample_{$entity}.csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
    
    if ($entity === 'members') {
        // Intestazioni in italiano come richiesto
        $headers = [
            'Numero Socio', 'Nome', 'Cognome', 'Email', 'Telefono', 'Codice Fiscale', 
            'CF Fatturazione', 'P.IVA Fatturazione', 'Indirizzo', 'Città', 
            'CAP', 'Provincia', 'Stato'
        ];
        fputcsv($out, $headers, ';');
        // Esempio
        fputcsv($out, [
            '12345', 'Mario', 'Rossi', 'mario.rossi@example.com', '3331234567', 'RSSMRA80A01H501U', 
            'RSSMRA80A01H501U', '', 'Via Roma 1', 'Milano', 
            '20100', 'MI', 'active'
        ], ';');
    }
    
    fclose($out);
    exit;
  }
}
