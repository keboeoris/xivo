<?php

/**
 * -------------------------------------------------------------------------
 * xivo plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of xivo.
 *
 * xivo is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * xivo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with xivo. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2017-2022 by xivo plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/xivo
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginXivoXuc {
   function getLoginForm() {
      // prepare a form for js submitting
       $out = '<form id="xuc_login_form">
        <div class="card">
            <div class="card-body">
                <fieldset class="form-fieldset">
                    <div class="mb-3">
                        <label for="xuc_username">'.__("XIVO username", 'xivo').'</label>
                        <input type="text" id="xuc_username" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="xuc_password">'.__("XIVO password", 'xivo').'</label>
                        <input type="password" id="xuc_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="xuc_phoneNumber">'.__("XIVO phone number", 'xivo').'</label>
                        <input type="text" id="xuc_phoneNumber" class="form-control">
                    </div>
                </fieldset>
                <div id="xuc_message"></div>
            </div>
            <div class="card-footer text-end">
                <input type="submit" class="submit btn btn-primary" id="xuc_sign_in" value="'.__("Connect").'">
            </div>
        </div>
        </form>';
      return $out;
   }

   function getLoggedForm() {
      $user = new User;
      $user->getFromDB($_SESSION['glpiID']);

       $initials = $user->getUserInitials();
       if($initials) {
           $avatar =  "<span class='avatar avatar-xl mb-3 rounded' style='display: flex; align-items: center; justify-content: center; background-color: #f0f0f0; color: #333; font-weight: bold; font-size: 1.5rem;'>$initials</span>";
       }

      $current_config = PluginXivoConfig::getConfig();

      $out = "<form id='xuc_logged_form'>
           <div class='card'>
           <div class='ribbon bg-red'><i id='xuc_sign_out' class='fa fa-power-off pointer'></i></div>
                  <div class='card-body p-4 text-center'>
                    ".$avatar."
                    <h3 class='m-0 mb-1'><a href='#' id='xuc_fullname'>Paweł Kuna</a></h3>";
       if ($current_config['enable_callcenter'] && PLUGIN_XIVO_ENABLE_CALLCENTER) {
           $out .= "<div>
                     <label for='xuc_user_status' class='form-label'>".__("User", 'xivo')."</label>
                     <select id='xuc_user_status'></select>
                  </div>";
       }
       $out .= "<div class='mt-3'>
                      <span class='badge bg-success-lt'><i class='ti ti-wifi me-2'></i> ".__("XIVO connected", 'xivo')."</span><br>
                      <p class='mt-2'><small class='text-muted'>".__("State").": </small><br>
                      <span class='badge bg-info-lt' id='xuc_phone_status' data-bs-toggle='tooltip' title='".__("Connected user's current phone status", 'xivo')."'>{{status}}</span>
                    </p>
                 </div>
                  </div>
                  <div class='table-responsive mb-2' id='xuc_call_informations'>
                    <table class='table table-vcenter table-bordered table-nowrap card-table'>
                        <tbody>
                            <tr class='bg-light' id='xuc_call_titles'>
                                <th class='subheader xuc_call_titles_th' colspan='4' id='xuc_ringing_title'>".__("Incoming call", 'xivo')."<span class='badge bg-primary ms-2 badge-blink'></span></th>
                                <th class='subheader xuc_call_titles_th' colspan='4' id='xuc_oncall_title'>".__("On call", 'xivo')."<span class='badge bg-danger ms-2 badge-blink'></span></th>
                                <th class='subheader xuc_call_titles_th' colspan='4' id='xuc_dialing_title'>".__("Dialing", 'xivo')."<span class='badge bg-info ms-2 badge-blink'></span></th>
                            </tr>
                            <tr>
                                <td>".__('Caller num:', 'xivo')."</td>
                                <td><span id='xuc_caller_num'></span></td>
                            </tr>
                            <tr>
                                <td>".__('Caller infos:', 'xivo')."</td>
                                <td><div id='xuc_caller_infos'></div></td>
                            </tr>
                        </tbody>
                    </table>
                  </div>
                  <div class='xuc_content'>
                    <div class='manual_actions'>
                        <div class='input-icon mb-3' id='dial_phone_num_container'>
                                    <span class='input-icon-addon'>
                                      <!-- Download SVG icon from http://tabler-icons.io/i/phone -->
                                        <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-phone'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2' /></svg>                                </span>
                                    <input type='text' class='form-control' placeholder='".__("Dial number", 'xivo')."' id='dial_phone_num'>
                        </div>
                        <div class='input-icon mb-3' id='transfer_phone_num_container' style='display: none;'>
                                    <span class='input-icon-addon'>
                                      <!-- Download SVG icon from http://tabler-icons.io/i/phone -->
                                        <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-phone'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2' /></svg>                                </span>
                                    <input type='text' class='form-control' placeholder='".__("Transfer to number", 'xivo')."' id='transfer_phone_num'>
                        </div>
                    </div>
                   </div>
                   <div id='auto_actions'>
                    <div class='row justify-content-center'>
                         <div class='col-auto'>
                            <a href='#' class='xuc_call_actions_btn btn btn-x w-100 btn-icon' id='xuc_answer' data-bs-toggle='tooltip' title='".__("Answer", 'xivo')."'><!-- Download SVG icon from http://tabler-icons.io/i/pause-circle -->
                                <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-phone-check'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2' /><path d='M15 6l2 2l4 -4' /></svg>
                               
                            </a>
                        </div>
                        <div class='col-auto'>
                            <a href='#' class='xuc_call_actions_btn btn btn-x w-100 btn-icon' id='xuc_hangup' data-bs-toggle='tooltip' title='".__("Hangup", 'xivo')."'><!-- Download SVG icon from http://tabler-icons.io/i/pause-circle -->
                                <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-phone-off'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M3 21l18 -18' /><path d='M5.831 14.161a15.946 15.946 0 0 1 -2.831 -8.161a2 2 0 0 1 2 -2h4l2 5l-2.5 1.5c.108 .22 .223 .435 .345 .645m1.751 2.277c.843 .84 1.822 1.544 2.904 2.078l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a15.963 15.963 0 0 1 -10.344 -4.657' /></svg>
                                
                            </a>
                        </div>
                        <div class='col-auto'>
                            <a href='#' class='xuc_call_actions_btn btn btn-x w-100 btn-icon' id='xuc_hold' data-bs-toggle='tooltip' title='".__("Hold", 'xivo')."'><!-- Download SVG icon from http://tabler-icons.io/i/pause-circle -->
                                <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-player-pause'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M6 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z' /><path d='M14 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z' /></svg>        
                                
                            </a>   
                        </div>
                    </div>
                  </div>
                  <div class='d-flex' id='xuc_call_actions'>
                  <a href='#' class='card-btn' id='xuc_transfer'><!-- Download SVG icon from http://tabler-icons.io/i/transfer -->
                        <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-transfer'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M20 10h-16l5.5 -6' /><path d='M4 14h16l-5.5 6' /></svg> 
                              ".__("Transfer", 'xivo')."</a>
                    <a href='#' class='card-btn' id='xuc_dial'><!-- Download SVG icon from http://tabler-icons.io/i/phone -->
                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon me-2 text-muted'><path stroke='none' d='M0 0h24v24H0z' fill='none'></path><path d='M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2'></path></svg>
                              ".__("Dial", 'xivo')."</a>
                  </div>
                </div>
            </form>
      </div>";

      return $out;
   }

   function getCallLink($users_id = 0) {
      $data = [
         'phone'          => null,
         'phone2'         => null,
         'mobile'         => null,
         'title'          => '',
      ];
      $user = new User;
      if ($user->getFromDB($users_id)) {
         if (!empty($user->fields['phone'])) {
            $data['phone']  = $user->fields['phone'];
            $data['phone2'] = $user->fields['phone2'];
            $data['mobile'] = $user->fields['mobile'];
            $data['title']  = sprintf(__("Call %s: %s"), $user->getName(), $user->fields['phone']);
         }
      }

      return $data;
   }

   function getUserInfosByPhone($params = []) {
      global $DB;

      $data = [
         'users'    => [],
         'tickets'  => [],
         'redirect' => false,
         'message'  => null
      ];

      $caller_num = isset($params['caller_num'])
         ? preg_replace('/\D+/', '', $params['caller_num']) // only digits
         : 0;

      if (empty($caller_num)) {
         return $data;
      }

      $r_not_digit = "[^0-9]*";
      $regex_num = "^".$r_not_digit.implode($r_not_digit, str_split($caller_num)).$r_not_digit."$";

      // try to find user by its phone or mobile numbers
      $iterator_users = $DB->request([
         'SELECT' => ['id'],
         'FROM'  => 'glpi_users',
         'WHERE' => [
            'OR' => [
               'phone'  => ['REGEXP', $regex_num],
               'mobile' => ['REGEXP', $regex_num],
            ]
         ]
      ]);
      foreach ($iterator_users as $data_user) {
         $userdata = getUserName($data_user["id"], 2);
         $name     = "<b>".__("User found in GLPI:", 'xivo')."</b>".
                     "&nbsp;".$userdata['name'];
         $name     = sprintf(__('%1$s %2$s'), $name,
                             Html::showToolTip($userdata["comment"],
                                               ['link'    => $userdata["link"],
                                                'display' => false]));

         $data_user['link'] = $name;
         $data['users'][]   = $data_user;
      }

      // one user search for tickets
      if (count($data['users']) > 1) {
         // mulitple user, no redirect and return a message
         $data['message'] = __("Multiple users found with this phone number", 'xivo');
      } else if (count($data['users']) == 1) {
         $current_user     = current($data['users']);
         $users_id         = $current_user['id'];
         $iterator_tickets = $DB->request([
            'SELECT'     => ['glpi_tickets.id', 'glpi_tickets.name', 'glpi_tickets.content'],
            'FROM'       => 'glpi_tickets',
            'INNER JOIN' => [
               'glpi_tickets_users' => [
                  'FKEY' => [
                     'glpi_tickets_users' => 'tickets_id',
                     'glpi_tickets'       => 'id',
                  ]
               ]
            ],
            'WHERE'     => [
               'glpi_tickets_users.type' => CommonITILActor::REQUESTER,
               'glpi_tickets.status'     => ["<", CommonITILObject::SOLVED],
            ],
         ]);
         $data['tickets'] = iterator_to_array($iterator_tickets);
         $nb_tickets = count($iterator_tickets);

         $ticket = new Ticket;
         $user   = new User;
         $user->getFromDB($users_id);

         if ($nb_tickets == 1) {
            // if we have one user with one ticket, redirect to ticket
            $ticket->getFromDB(current($data['tickets'])['id']);
            $data['redirect'] = $ticket->getLinkURL();
         } else if ($nb_tickets > 1) {
            // if we have one user with multiple tickets, redirect to user (on Ticket tab)
            $data['redirect'] = $user->getLinkURL().'&forcetab=Ticket$1';
         } else {
            // if the current user has no tickets, redirect to ticket creation form
            $data['redirect'] = $ticket->getFormUrl().'?_users_id_requester='.$user->getID();
         }
      }

      return $data;
   }
}