# uk.co.compucorp.membershipperiod

![Screenshot](/images/screenshot.png)

This extension records membership periods/terms relevant data in separate entity when creating or renewing a membership. The recorded details are observable through membership "View" interface.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7.28

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation using manual zip file extraction

1. Download the zip file from the Github repository
2. Extract the zip file downloaded above. The extraction should contain a folder named "uk.co.compucorp.membershipperiod" and copy that folder in to the CiviCRM extensions directory
3. Log in to CiviCRM and visit Administrator -> System Settings -> Extensions
4. Click on "Refresh" button
5. The new extension name "Membership Period" should appear on the extensions list
6. Click "Install" and from the next page again click on "Install"

## Installation (CLI, Zip) using cv

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <civicrm-extension-dir>
cv dl uk.co.compucorp.membershipperiod@https://github.com/coolbit/uk.co.compucorp.membershipperiod/archive/master.zip
```

## Usage
The flow of membership creation and renewal are not altered in any way from the user's perspective. In order to view the recorded membership periods,
1. Log in to CiviCRM
2. Visit in main menu, Memberships -> Find Memberships
3. Find the target membership
4. Click on "View" membership link in the result membership row

In the membership view interfaces, there should be a separate section called "Membership Period Details" with the membership period details of the selected member

## Known Issues
The membership period details will not change when "Edit" memberships (not Renewals). The edit interfaces allows manual membership start and end date edits and that gets complicated with the membership history. The implementation needs a technical and logical conversation with the core team to finalize a suitable logic.
