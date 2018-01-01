# uk.co.compucorp.membershipperiod

![Screenshot](/images/screenshot.png)

This extension records membership periods/terms relevant data in separate entity when creating or renewing a membership. The recorded details are observable through membership "View" pop-up interface.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7.28

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl uk.co.compucorp.membershipperiod@https://github.com/coolbit/uk.co.compucorp.membershipperiod/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/uk.co.compucorp.membershipperiod.git
cv en membershipperiod
```

## Usage
The flow of membership creation and renewal are not altered in any way from the user's perspective. In order to view the recorded membership periods,
1. Log in to CiviCRM
2. Visit in main menu, Memberships -> Find Memberships
3. Find the target membership
4. Click on "View" membership link in the result membership row

In the membership view pop-up, there should be a separate section called "Membership Period Details" with the membership period details of the selected member

## Known Issues
The membership period details will not change when "Edit" memberships (not Renewals). The edit interfaces allows manual membership start and end date edits and that gets complicated with the membership history. The implementation needs a technical and logical conversation with the core team to finalize a suitable logic.
