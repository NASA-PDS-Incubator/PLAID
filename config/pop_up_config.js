/**
 * Copyright 2017 California Institute of Technology
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * @file An isolated location to store configurations for the pop-up windows used throughout PLAID. The main intent
 * of this file is to interact closely with the generatePopUp method found in pop_up.js, supplying it with
 * the content and functions it requires to dynamically construct unique pop-ups for any given situation.
 *
 * Note: Many yesFunctions will redirect the window upon click in order to navigate the user to the next process.
 * Note: The generatePopUp method does not need to use objects from this file to create pop-ups, the Objects can be
 *       constructed anywhere. This comes in handy when a pop-up calls for content or functions that cannot be
 *       defined statically.
 *
 * Creation Date: 7/25/16.
 *
 * @author Michael Kim
 * @author Trevor Morse
 * @author Stirling Algermissen
 */
const MAX_FILE_SIZE =  4 * 1024 * 1024;    // 4 Megabytes
// 'XML_FILE_TYPE' is already defined by another file that includes this one
const XML_FILE_TYPE_CONST = "text/xml";
const CREATE_NEW_CHOICE = "create_new";  // RadioButton choices
const IMPORT_FILE_CHOICE = "import_file";
const IMPORT_SAMPLE_CHOICE = "import_sample";
// Global variables
let g_creationChoice = null;    // Create New, Step 1 radiobutton choice

var popUpData = {
    // These variables, currentStep and newStep, are used to store the initial step change attempt made by the user,
    // which is referenced again later in the pop-up's yesFunctions that have intent to mimic the same step change
    currentStep : 0,
    newStep : 0,
    // When a user is leaving in the middle of constructing a single attribute, this pop-up warns the user that
    // the progress made for this particular single attribute will not be saved
    addAttr : {
        id : "addAttr",
        title : "Warning",
        content : "<div>Leaving at this step will delete any unsaved progress for this single attribute.</div><br>" +
        "<div>Click 'Continue' to leave anyway.</div>",
        noText : "Cancel",
        yesText : "Continue",
        /**
         * When a user confirms to leave out of this phase of the mission specifics builder, use jQuery to click the
         * step on the left toolbar representing the desired new location. To leave the page without triggering
         * the same pop-up once again, a leaving class is added to the HTML. This flag is processed in updatePopUp of
         * the pop_up.js file.
         */
        yesFunction : function() {
            $('#addAttr').modal('hide');
            var section = $("#wizard-p-" + popUpData.currentStep.toString());
            $(section).find("[pop-up]").each(function() {
                $(this).addClass(" leaving");
            });
            // Turn on the HTML class that skips the check for pop-ups, allowing the wizard to step
            var section = $("#wizard-p-" + popUpData.currentStep.toString());
            $(section).addClass("leaving");
            var newStep = "#wizard-t-" + popUpData.newStep.toString();
            $(newStep).click();
        }
    },
    // When a user is leaving in the middle of constructing a group of attributes, this pop-up warns the user that
    // the progress made for this particular group will not be saved
    addGroup : {
        id : "addGroup",
        title : "Warning",
        content : "<div>Leaving at this step will delete any unsaved progress for this attribute group.</div><br>" +
        "<div>Click 'Continue' to leave anyway.</div>",
        noText : "Cancel",
        yesText : "Continue",
        /**
         * See doc for yesFunction in addAttr object
         */
        yesFunction : function() {
            $('#addGroup').modal('hide');
            var section = $("#wizard-p-" + popUpData.currentStep.toString());
            $(section).find("[pop-up]").each(function() {
                $(this).addClass(" leaving");
            });
            // Turn on the HTML class that skips the check for pop-ups, allowing the wizard to step
            var section = $("#wizard-p-" + popUpData.currentStep.toString());
            $(section).addClass("leaving");
            var newStep = "#wizard-t-" + popUpData.newStep.toString();
            $(newStep).click();
        }
    },
    // When a user incorrectly fills out a choice section of optional PDS4-attributes, disallow the step change
    // and force the user to complete the form correctly
    invalidChoice : {
        id : "invalidChoice",
        title : "Invalid Choice",
        content : "Please properly select the elements in the choice section.",
        noText: "",
        yesText: "OK",
        /**
         * Hides the pop-up once the user confirms that an error exists
         */
        yesFunction : function() {
            $('#invalidChoice').modal('hide');
        }
    },
    // When a user attempts to create a new label from the dashboard, this pop-up forces the user to choose
    // to either create a new label, import an XML file, or import a sample label
    createNewLabelMultiModalStep1 : {
        id : "createNewLabelMultiModalStep1",
        title : "Create New Label, Step 1",
        content : "<form>" +
        "<div class='form-group'><input type='radio' id='creationChoice1' name='creation' value='" + CREATE_NEW_CHOICE + "' checked>" +
        "<label for='creationChoice1'>Create New</label></div>" +
        "<div class='form-group'><input type='radio' id='creationChoice2' name='creation' value='" + IMPORT_FILE_CHOICE + "'>" +
        "<label for='creationChoice2'>Import XML File</label></div>" +
        "<div class='form-group'><input type='radio' id='creationChoice3' name='creation' value='" + IMPORT_SAMPLE_CHOICE + "'>" +
        "<label for='creationChoice3'>Import Sample label</label></div>" +
        "</form>",
        noText: "Cancel",
        yesText: "Continue",
        /**
         * Captures user's selection for type of new label creation, and calls the Step 2 modal dialog
         */
        yesFunction : function() {
            // Get the radio button choice
            g_creationChoice = $("input[name='creation']:checked").val();
            //console.log('Creation Choice =', g_creationChoice);
            // Hide the Step 1 dialog after validation
            $('#createNewLabelMultiModalStep1').modal('hide');
            // Pop up the "Create New Label, Step 2" modal dialog
            generatePopUp(popUpData['createNewLabelMultiModalStep2']);
            // Initialize the Schema Version dropdown and Basic/Advanced Mode
            initializeCreateNewDialogSchemaVersionAndMode();
            // IF Create New button was selected
            if (g_creationChoice === CREATE_NEW_CHOICE) {
                //console.log('Will Pop up the Create New Label Only dialog');
                // Hide the XML File selector
                $("#xml_file_div").hide();
                // Hide the Sample Label dropdown list
                $("#sample_label_select_div").hide();
            } else if (g_creationChoice === IMPORT_FILE_CHOICE) {
                //console.log('Will Pop up the Import XML File dialog');
                // Show the XML File selector
                $("#xml_file_div").show();
                // Hide the Sample Label dropdown list
                $("#sample_label_select_div").hide();
            } else if (g_creationChoice === IMPORT_SAMPLE_CHOICE) {
                //console.log('Will Pop up the Import Sample Label dialog');
                // Hide the XML File selector
                $("#xml_file_div").hide();
                // Show the Sample Label dropdown list
                $("#sample_label_select_div").show();
                // Initialize the Sample Label dropdown list
                initializeCreateNewDialogSampleLabelDropdown();
            }
        }
    },
    // When a user attempts to create a new label from the dashboard, this pop-up can allow the user to specify an
    // XML File to import, or a Sample Label to import
    createNewLabelMultiModalStep2 : {
        id : "createNewLabelMultiModalStep2",
        title : "Create New Label, Step 2",
        content : "<form><div class='form-group'><label for='labelNameInput'>Please enter a name for your new label:</label>" +
        "<input id='labelNameInput' class='form-control' type='text' placeholder='Ex. My Mission Label' id='example-text-input'></div>" +
        "<div class='form-group'><label for='core_schema_version_select'>Please select a PDS4 schema version:</label>" +
        "<select class='form-control' id='core_schema_versions_select'></select></div>" +
        "<div class='form-group'><label>Mode: &nbsp;</label><input id='basic_mode_create_toggle' type='checkbox' checked data-toggle='toggle' data-on='Basic' data-off='Advanced' data-width='120'></div>" +
        "<div class='form-group' id='xml_file_div'><label>XML File to Import: &nbsp;</label><input id='xml_file_to_import' name='xml_file_to_import' type='file'></div>" +
        "<div class='form-group' id='sample_label_select_div'><label for='sample_label_select'>Please select a sample label to import:</label>" +
        "<select class='form-control' id='sample_label_select'></select></div>" +
        "</form>",
        noText: "Cancel",
        yesText: "Continue",
        /**
         * Captures user's input for new label Name, Schema version, Basic/Advanced mode, XML File to import,
         * OR Sample Label to import
         * If file selected, sends it to the server
         */
        yesFunction : function() {
            if (isValidLabelNameInput($('#labelNameInput'))) {
                // IF the Create New label radiobutton was selected
                if (g_creationChoice === CREATE_NEW_CHOICE) {
                    // Hide this Step 2 dialog after validation
                    $('#createNewLabelMultiModalCreateNewLabelOnlyStep').modal('hide');
                    // Create a new label database entry on server
                    createNewLabelInDB();
                } else if (g_creationChoice === IMPORT_FILE_CHOICE) {
                    // Get the file info. from the file input control
                    const fileData = $("#xml_file_to_import").prop('files')[0];
                    //console.log('fileData =', fileData);
                    // IF the user selected a file to import
                    if (fileData != undefined) {
                        const fileName = fileData.name;
                        //console.log('File name =', fileName);
                        const fileSize = fileData.size;
                        //console.log('File size =', fileSize);
                        // IF the selected file's size over the limit
                        if (fileSize > MAX_FILE_SIZE) {
                            $("#xml_file_to_import").addClass("error");
                            //alert('Selected file size of ' + fileSize + ' > max. file size of ' + MAX_FILE_SIZE);
                            console.log('Error: Selected file size of ', fileSize, ' > max. file size of ', MAX_FILE_SIZE);
                            return;
                        }
                        const fileType = fileData.type;
                        //console.log('File type =', fileType);
                        // IF the selected file's type is NOT XML
                        if (!(fileType === XML_FILE_TYPE_CONST)) {
                            $("#xml_file_to_import").addClass("error");
                            //alert('Selected file type is ' + fileType + ', not ' + XML_FILE_TYPE_CONST);
                            console.log('Error: Selected file type is ', fileType, ', not ', XML_FILE_TYPE_CONST);
                            return;
                        }
                        // Hide this Step 2 dialog after validation
                        $('#createNewLabelMultiModalStep2').modal('hide');
                        $("#xml_file_to_import").removeClass("error");

                        // Create a new label database entry on server
                        createNewLabelInDB(fileData);
                    }
                } else if (g_creationChoice === IMPORT_SAMPLE_CHOICE) {
                    // Get the selected Sample Label
                    const selectedSampleLabelId = $("#sample_label_select").val();
                    //console.log('Selected sample label ID is', selectedSampleLabelId);
                    // IF NO Sample label was selected
                    if ((selectedSampleLabelId == null) || (selectedSampleLabelId <= 0)) {
                        $("#sample_label_select").addClass("error");
                        return;
                    }
                    // Hide this Step 2 dialog after validation
                    $('#createNewLabelMultiModalStep2').modal('hide');
                    $("#sample_label_select").removeClass("error");

                    // Create a new label database entry on server
                    createNewLabelInDB(null, selectedSampleLabelId);
                }       // end IF-Else on radiobutton choice
            }       // end IF a new label name was given
        }
    },
    // When a user attempts to change the label from a previously visited step, give the user the option to save
    // the change and lose the proceeding progress or to revert the changes made
    deleteProgress : {
        id : "deleteProgress",
        title : "Warning",
        content : "<div>You have made a change. Would you like to keep the change or revert it?</div>"+
            "<br/><div><i>Note: If you keep the change, all progress will be lost after this point."+
            " If you revert, then the changes will be reverted and you will advance to the next step.</i></div>",
        noText : "Revert Changes",
        yesText : "Continue",
        /**
         * Removes the progressData proceeding this step and stores the new changes
         * After, a backend call is made to remove root attributes
         * Finally, the window is reloaded for a proper load of the newly formed progress
         */
        yesFunction : function(){
            var type = progressData[wizardData.currentStep]['step'];
            progressData = progressData.slice(0, wizardData.currentStep);
            storeProgress(wizardData.currentStep, type);
            backendCall("php/xml_mutator.php",
                "removeRootAttrs",
                {namespaces: g_jsonData.namespaces},
                function(data){});
            location.reload(true);
        },
        /**
         * Returns the user to the same step using the unchanged progressData
         */
        noFunction : function(){
            loadProgress(progressData[wizardData.currentStep]);
        }
    },
    // When a user wants to preview the
    // Note: The content for this pop-up is set dynamically from where it calls the generate method
    preview: {
        id : "preview",
        title : "Label Template Preview",
        content : "",
        noText : "",
        yesText : "Dismiss",
        /**
         * Hides the pop-up
         */
        yesFunction : function(){
            $('#preview').modal('hide');
        }
    }
};
