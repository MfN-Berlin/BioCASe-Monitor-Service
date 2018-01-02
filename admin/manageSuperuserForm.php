<?php
/**
 * BioCASe Monitor 2.1
 * @copyright (C) 2013-2017 www.mfn-berlin.de
 * @author  thomas.pfuhl@mfn-berlin.de
 * based on Version 1.4 written by falko.gloeckler@mfn-berlin.de
 *
 * @file biocasemonitor/admin/manageSuperuserForm.php
 * @brief backend: manage Superuser (html form)
 *
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
if (false) {

}
?>
<div id="main">
    <h1 class="hint">
        Manage All User Profiles
    </h1>

    <h2>Authentication & Rights</h2>
    <ul><li>password is user-specific, rights are provider-specific and user-specific.

        <li>user.password a salted md5 hash
        <li>auth.rights is a bitmask for CRUD ( create, read, update, delete):
    </ul>
    <pre style="font-family: courier"  >
    no access:      ---- = 0+0+0+0 = 0
    read only:      -r-- = 0+2+0+0 = 2
    read+update:    -ru- = 0+2+4+0 = 6
    crud:           crud = 1+2+4+8 = 15
    superuser:      crudx= 1+2+4+8+16 = 31 (highest bit is relevant)
    </pre>

    <?php
    echo "<h2>Provider IDs</h2>";
    echo "<ul style='margin-left:50px;'>";
    foreach ($providerdata as $providerprofile) {
        echo "<li>" . $providerprofile["id"] . ". " . $providerprofile["name"] . "</li>";
    }
    echo "</ul>";
    ?>


    <h2>Users</h2>
    <form id="updateAllUsers" name="updateAllUsers" method="POST" action="manageUser.php">
        <input type="hidden" name="formsent"  value="1"/>
        <input type="hidden" name="adduser"  id="adduser" value="0"/>
        <input type="hidden" name="me"  value="<?php echo $_SESSION["username"] ?>"/>

        <table>
            <tr><th>username<th>password<th>fullname<th>email<th>provider<th>rights</tr>
            <?php
            for ($i = 0; $i < count($userdata); $i++) {
                $userprofile = $userdata[$i];
                ?>
                <tr>
                    <td><input type="text" name="profile[<?php echo $i ?>][username]"
                               title="username" size="10" value="<?php echo $userprofile["username"] ?>"
                               placeholder="Username"
                               required /></td>

                    <td><input type="text" name="profile[<?php echo $i ?>][password]"
                               title="password" value="<?php echo $userprofile["password"] ?>"
                               placeholder="Password"/>
                        <br/>
                        <input type="text" name="profile[<?php echo $i ?>][newpassword]"
                               title="new password" size="12" value=""
                               placeholder="New Password"/>
                        <br/>
                        <input type="text" name="profile[<?php echo $i ?>][newpassword2]"
                               title="retype password" size="12" value=""
                               placeholder="New Password"/></td>

                    <td><input type="text" name="profile[<?php echo $i ?>][fullname]"
                               title="full name" size="30" value="<?php echo $userprofile["fullname"] ?>"
                               placeholder="Full Name"/></td>
                    <td><input type="text" name="profile[<?php echo $i ?>][email]"
                               title="email address" size="30" value="<?php echo $userprofile["email"] ?>"
                               placeholder="Email"
                               required/></td>
                    <td><input type="text" name="profile[<?php echo $i ?>][provider]"
                               title="provider" size="3" value="<?php echo $userprofile["institution_id"] ?>"
                               placeholder="Provider"
                               class="numeric"
                               required/></td>
                    <td><input type="text" name="profile[<?php echo $i ?>][rights]"
                               title="rights" size="3" value="<?php echo $userprofile["rights"] ?>"
                               placeholder="Rights"
                               class="numeric"
                               required/></td>
                </tr>
                <?php
            }
            ?>
            <tr><td>
                    <a href='#'
                       onclick='$("#adduser").val("1"); $("#addUserMask").show()'>
                        <img class='update' alt='addUser' title='add user' src='../images/glyphicons/glyphicons-433-plus.png' />
                    </a>
            </tr>

            <tr id="addUserMask" style="display:none">
                <td><input type="text" name="profile[<?php echo count($userdata) ?>][username]"
                           title="username" size="10"
                           placeholder="Username"  /></td>
                <td> </td>
                <td><input type="text" name="profile[<?php echo count($userdata) ?>][newpassword]"
                           title="new password"  size="12"
                           placeholder="New Password"/></td>
                <td><input type="text" name="profile[<?php echo count($userdata) ?>][newpassword2]"
                           title="retype password"  size="12"
                           placeholder="Retype Passwd"/></td>
                <td><input type="text" name="profile[<?php echo count($userdata) ?>][fullname]"
                           title="full name" size="30"
                           placeholder="Full Name"/></td>
                <td><input type="text" name="profile[<?php echo count($userdata) ?>][email]"
                           title="email address"  size="30"
                           placeholder="Email" /></td>
                <td><input type="text" name="profile[<?php echo count($userdata) ?>][provider]"
                           title="provider"  size="3" class="numeric"
                           placeholder="Provider" /></td>
                <td><input type="text" name="profile[<?php echo count($userdata) ?>][rights]"
                           title="rights"  size="3" class="numeric"
                           placeholder="Rights" /></td>
            </tr>
        </table>

        <div><input type="submit" name="submit" value="validate"/></div>
    </form>

    <div id="system-message"></div>
</div>

