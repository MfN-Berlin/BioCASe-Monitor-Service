<?php

/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2018 www.museumfuernaturkunde.berlin
 * @author  thomas.pfuhl@mfn.berlin
 * based on Version 1.4 written by falko.gloeckler@mfn.berlin
 *
 * @file biocasemonitor/admin/manageUserForm.php
 * @brief backend: manage user (html form)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>

<div class="container">
    <div class="row">
        <div class="col-md-12 alert alert-info">
            My Profile
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">

            <form id="updateUser" name="updateUser" method="POST" action="manageUser.php">
                <input type="hidden" name="formsent" value="1" />
                <input type="hidden" name="me" value="<?php echo $_SESSION["username"] ?>" />

                <input type="hidden" name="profile[0][username]" title="username" value="<?php echo $_SESSION["username"] ?>" />

                <input type="hidden" name="profile[0][password]" title="password" value="<?php echo $userdata[0]["password"] ?>" />

                <div>Username: <br><span style='color:#666'><?php echo $_SESSION["username"] ?></span></div>
                <div>Data Center: <br><span style='color:#666'><?php echo $_SESSION["provider"] ?></span></div>
                <div>Last Connection: <br><span style='color:#666'><?php echo date("Y-m-d", $userdata[0]["last_connection"]) ?></span></div>

                <div class="form-group">
                    Password:<br />
                    <input type="password" name="profile[0][newpassword]" id="pw" title="new password" size="20" value="" placeholder="New Password" />
                </div>
                <div class="form-group">
                    Retype Password:<br />
                    <input type="password" name="profile[0][newpassword2]" id="pw2" title="please retype your password" size="20" value="" placeholder="Retype Password" />
                </div>
                <div class="form-group">
                    Full Name: <br>
                    <input type="text" name="profile[0][fullname]" title="full name" size="30" value="<?php echo $userdata[0]["fullname"] ?>" placeholder="Full Name" />
                </div>
                <div class="form-group">
                    Email: <br>
                    <input type="email" name="profile[0][email]" title="email address" size="30" value="<?php echo $userdata[0]["email"] ?>" placeholder="Email" required />
                </div>

                <div class="form-group">
                    Avatar: <br>
                    <input type="text" name="profile[0][avatar]" title="avatar" size="30" value="<?php echo $userdata[0]["avatar"] ?>" placeholder="URL holding your avatar" />
                </div>


                <button type="submit" class="btn btn-default">Submit</button>

            </form>
        </div>
    </div>
</div>';

<div id="system-message"></div>