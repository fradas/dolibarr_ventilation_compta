<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file      accountingex/class/Accountingaccount.class.php
 * \ingroup   Accounting Expert
 * \brief     Fichier de la classe des comptes comptable
 */

/**
 * \class AccountingAccount
 * \brief Classe permettant la gestion des comptes generaux de compta
 */
class AccountingAccount {
	var $db;
	var $id;
	var $rowid;
	var $datec; // Creation date
	var $fk_pcg_version;
	var $pcg_type;
	var $pcg_subtype;
	var $account_number;
	var $account_parent;
	var $label;
	var $fk_user_author;
	var $fk_user_modif;
	var $active;

	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	function __construct($db, $rowid = '') {

		$this->db = $db;
		
		if ($rowid != '')
			return $this->fetch ( $rowid );
	}

	/**
	 * \brief Load record in memory
	 */
	function fetch($rowid = null, $account_number = null) {

		if ($rowid || $account_number) {
			$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "accountingaccount WHERE ";
			if ($rowid) {
				$sql .= " rowid = '" . $rowid . "'";
			} elseif ($account_number) {
				$sql .= " account_number = '" . $account_number . "'";
			}
			
			dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
			$result = $this->db->query ( $sql );
			if ($result) {
				$obj = $this->db->fetch_object ( $result );
			} else {
				return null;
			}
		}
		
		$this->id = $obj->rowid;
		$this->rowid = $obj->rowid;
		$this->datec = $obj->datec;
		$this->tms = $obj->tms;
		$this->fk_pcg_version = $obj->fk_pcg_version;
		$this->pcg_type = $obj->pcg_type;
		$this->pcg_subtype = $obj->pcg_subtype;
		$this->account_number = $obj->account_number;
		$this->account_parent = $obj->account_parent;
		$this->label = $obj->label;
		$this->fk_user_author = $obj->fk_user_author;
		$this->fk_user_modif = $obj->fk_user_modif;
		$this->active = $obj->active;
		
		return $obj->rowid;
	}

	/**
	 * \brief insert line in accountingaccount
	 * \param user utilisateur qui effectue l'insertion
	 */
	function create($user, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		if (isset ( $this->fk_pcg_version ))
			$this->fk_pcg_version = trim ( $this->fk_pcg_version );
		if (isset ( $this->pcg_type ))
			$this->pcg_type = trim ( $this->pcg_type );
		if (isset ( $this->pcg_subtype ))
			$this->pcg_subtype = trim ( $this->pcg_subtype );
		if (isset ( $this->account_number ))
			$this->account_number = trim ( $this->account_number );
		if (isset ( $this->account_parent ))
			$this->account_parent = trim ( $this->account_parent );
		if (isset ( $this->label ))
			$this->label = trim ( $this->label );
		if (isset ( $this->fk_user_author ))
			$this->fk_user_author = trim ( $this->fk_user_author );
		if (isset ( $this->active ))
			$this->active = trim ( $this->active );
			
			// Check parameters
			// Put here code to add control on parameters values
			
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "accountingaccount(";
		
		$sql .= "datec";
		$sql .= ", entity";
		$sql .= ", fk_pcg_version";
		$sql .= ", pcg_type";
		$sql .= ", pcg_subtype";
		$sql .= ", account_number";
		$sql .= ", account_parent";
		$sql .= ", label";
		$sql .= ", fk_user_author";
		$sql .= ", active";
		
		$sql .= ") VALUES (";
		
		$sql .= " '" . $this->db->idate ( $now ) . "'";
		$sql .= ", " . $conf->entity;
		$sql .= ", " . (! isset ( $this->fk_pcg_version ) ? 'NULL' : "'" . $this->db->escape ( $this->fk_pcg_version ) . "'");
		$sql .= ", " . (! isset ( $this->pcg_type ) ? 'NULL' : "'" . $this->db->escape ( $this->pcg_type ) . "'");
		$sql .= ", " . (! isset ( $this->pcg_subtype ) ? 'NULL' : "'" . $this->pcg_subtype . "'");
		$sql .= ", " . (! isset ( $this->account_number ) ? 'NULL' : "'" . $this->account_number . "'");
		$sql .= ", " . (! isset ( $this->account_parent ) ? 'NULL' : "'" . $this->db->escape ( $this->account_parent ) . "'");
		$sql .= ", " . (! isset ( $this->label ) ? 'NULL' : "'" . $this->db->escape ( $this->label ) . "'");
		$sql .= ", " . $user->id;
		$sql .= ", " . (! isset ( $this->active ) ? 'NULL' : "'" . $this->db->escape ( $this->active ) . "'");
		
		$sql .= ")";
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		if (! $error) {
			$this->id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "accountingaccount" );
			
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
				
				// // Call triggers
				// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::create " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return $this->id;
		}
	}

	/**
	 * Update record
	 *
	 * @param User $user update
	 * @return int if KO, >0 if OK
	 */
	function update($user) {

		global $langs;
		
		$this->db->begin ();
		
		$sql = "UPDATE " . MAIN_DB_PREFIX . "accountingaccount ";
		$sql .= " SET fk_pcg_version = " . ($this->fk_pcg_version ? "'" . $this->db->escape ( $this->fk_pcg_version ) . "'" : "null");
		$sql .= " , pcg_type = " . ($this->pcg_type ? "'" . $this->db->escape ( $this->pcg_type ) . "'" : "null");
		$sql .= " , pcg_subtype = " . ($this->pcg_subtype ? "'" . $this->db->escape ( $this->pcg_subtype ) . "'" : "null");
		$sql .= " , account_number = '" . $this->account_number . "'";
		$sql .= " , account_parent = '" . $this->account_parent . "'";
		$sql .= " , label = " . ($this->label ? "'" . $this->db->escape ( $this->label ) . "'" : "null");
		$sql .= " , fk_user_modif = " . $user->id;
		$sql .= " , active = '" . $this->active . "'";
		
		$sql .= " WHERE rowid = " . $this->id;
		
		dol_syslog ( get_class ( $this ) . "::update sql=" . $sql, LOG_DEBUG );
		$result = $this->db->query ( $sql );
		if ($result) {
			$this->db->commit ();
			return 1;
		} else {
			$this->error = $this->db->lasterror ();
			$this->db->rollback ();
			return - 1;
		}
	}

	/**
	 * Update record
	 *
	 * @param User $user update
	 * @return int if KO, >0 if OK
	 */
	function desactivate($user) {

		global $langs;
		
		$result = $this->checkUsage ();
		
		if ($result > 0) {
			$this->db->begin ();
			
			$sql = "UPDATE " . MAIN_DB_PREFIX . "accountingaccount ";
			$sql .= "SET active = '0'";
			
			$sql .= " WHERE rowid = " . $this->id;
			
			dol_syslog ( get_class ( $this ) . "::desactivate sql=" . $sql, LOG_DEBUG );
			$result = $this->db->query ( $sql );
			if ($result) {
				$this->db->commit ();
				return 1;
			} else {
				$this->error = $this->db->lasterror ();
				$this->db->rollback ();
				return - 1;
			}
		} else {
			return - 1;
		}
	}

	/**
	 * Update record
	 *
	 * @param User $user update
	 * @return int if KO, >0 if OK
	 */
	function activate($user) {

		global $langs;
		
		$this->db->begin ();
		
		$sql = "UPDATE " . MAIN_DB_PREFIX . "accountingaccount ";
		$sql .= "SET active = '1'";
		
		$sql .= " WHERE rowid = " . $this->id;
		
		dol_syslog ( get_class ( $this ) . "::activate sql=" . $sql, LOG_DEBUG );
		$result = $this->db->query ( $sql );
		if ($result) {
			$this->db->commit ();
			return 1;
		} else {
			$this->error = $this->db->lasterror ();
			$this->db->rollback ();
			return - 1;
		}
	}

	/**
	 * Check usage of accounting code
	 *
	 * @param User $user update
	 * @return int if KO, >0 if OK
	 */
	function checkUsage() {

		global $langs;
		
		$sql = "(SELECT fk_code_ventilation FROM " . MAIN_DB_PREFIX . "facturedet";
		$sql .= " WHERE  fk_code_ventilation=" . $this->id . ")";
		$sql .= "UNION";
		$sql .= "(SELECT fk_code_ventilation FROM " . MAIN_DB_PREFIX . "facture_fourn_det";
		$sql .= " WHERE  fk_code_ventilation=" . $this->id . ")";
		
		dol_syslog ( get_class ( $this ) . "::checkUsage sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if ($resql) {
			$num = $this->db->num_rows ( $resql );
			if ($num > 0) {
				$this->error = $langs->trans ( 'AccountancyCodeIsAlreadyUse' );
				return 0;
			} else {
				return 1;
			}
		} else {
			$this->error = $this->db->lasterror ();
			return - 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user that deletes
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;
		
		$result = $this->checkUsage ();
		
		if ($result > 0) {
			
			$this->db->begin ();
			
			if (! $error) {
				if (! $notrigger) {
					// Uncomment this and change MYOBJECT to your own tag if you
					// want this action calls a trigger.
					
					// // Call triggers
					// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
					// $interface=new Interfaces($this->db);
					// $result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
					// if ($result < 0) { $error++; $this->errors=$interface->errors; }
					// // End call triggers
				}
			}
			
			if (! $error) {
				$sql = "DELETE FROM " . MAIN_DB_PREFIX . "accountingaccount";
				$sql .= " WHERE rowid=" . $this->id;
				
				dol_syslog ( get_class ( $this ) . "::delete sql=" . $sql );
				$resql = $this->db->query ( $sql );
				if (! $resql) {
					$error ++;
					$this->errors [] = "Error " . $this->db->lasterror ();
				}
			}
			
			// Commit or rollback
			if ($error) {
				foreach ( $this->errors as $errmsg ) {
					dol_syslog ( get_class ( $this ) . "::delete " . $errmsg, LOG_ERR );
					$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
				}
				$this->db->rollback ();
				return - 1 * $error;
			} else {
				$this->db->commit ();
				return 1;
			}
		} else {
			return - 1;
		}
	}

	/**
	 * Information on record
	 *
	 * @param int $id of record
	 * @return void
	 */
	function info($id) {

		$sql = 'SELECT a.rowid, a.datec, a.fk_user_author, a.fk_user_modif, a.tms';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'accountingaccount as a';
		$sql .= ' WHERE a.rowid = ' . $id;
		
		dol_syslog ( get_class ( $this ) . '::info sql=' . $sql );
		$result = $this->db->query ( $sql );
		
		if ($result) {
			if ($this->db->num_rows ( $result )) {
				$obj = $this->db->fetch_object ( $result );
				$this->id = $obj->rowid;
				if ($obj->fk_user_author) {
					$cuser = new User ( $this->db );
					$cuser->fetch ( $obj->fk_user_author );
					$this->user_creation = $cuser;
				}
				if ($obj->fk_user_modif) {
					$muser = new User ( $this->db );
					$muser->fetch ( $obj->fk_user_modif );
					$this->user_modification = $muser;
				}
				$this->date_creation = $this->db->jdate ( $obj->datec );
				$this->date_modification = $this->db->jdate ( $obj->tms );
			}
			$this->db->free ( $result );
		} else {
			dol_print_error ( $this->db );
		}
	}
}
?>
