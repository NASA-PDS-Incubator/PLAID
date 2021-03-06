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
 * @file Contains global objects and lists that are referenced throughout
 * the project. This file provides a central place for tracking this data.
 *
 * Creation Date: 7/7/16.
 *
 * @author Trevor Morse
 * @author Michael Kim
 * @author Stirling Algermissen
 */



var default_schema = 1800;
var filePaths, g_dictInfo; // set when label is loaded

var node_contact_info = {
    cart: {
        node_name: "Cartography and Imaging Sciences Node",
        name: "Lisa Gaddis",
        email: "lgaddis@usgs.gov"
    },
    atmosphere: {
        node_name: "Planetary Atmospheres Node",
        name: "Nancy Chanover",
        email: "nchanove@nmsu.edu"
    },
    ppi: {
        node_name: "Planetary Plasma Interactions Node",
        name: "Raymond Walker",
        email: "rwalker@igpp.ucla.edu"
    },
    rsn: {
        node_name: "Radio Science Node",
        name: "Richard A. Simpson",
        email: "rsimpson@magellan.stanford.edu"
    },
    geo: {
        node_name: "Geosciences Node",
        name: "Raymond Arvidson",
        email: "arvidson@wunder.wustl.edu"
    },
    rms: {
        node_name: "Ring-Moon Systems Node",
        name: "Mark Showalter",
        email: "mshowalter@seti.org"
    },
    sbn : {
        node_name: "Small Bodies Node",
        name: "Michael F. A'Hearn",
        email: "ludmilla@astro.umd.edu"
    },
    test : {
        node_name: "PLAID Support",
        name: "PLAID Support",
        email: "plaid-support@jpl.nasa.gov"
    }

};

/**
 * This object is for storing the paths to the various JSON files for PDS4 and
 * discipline nodes. These JSONs control the dynamic creation of content in PLAID.
 * @type {{PDS_JSON: string, CART_JSON: string, DISP_JSON: string, GEOM_JSON: string, IMG_JSON: string, PART_JSON: string, RMS_JSON: string, BOD_JSON: string, SPECT_JSON: string, WAV_JSON: string}}
 */


var filePaths_1F00 = {
  PDS_JSON: "config/PDS4_ALL_1F00.JSON",
  CART_JSON: "config/PDS4_ALL_1F00.JSON",
  DISP_JSON: "config/PDS4_ALL_1F00.JSON",
  GEOM_JSON: "config/PDS4_ALL_1F00.JSON",
  IMG_JSON: "config/PDS4_ALL_1F00.JSON",
  PART_JSON: "config/PDS4_ALL_1F00.JSON",
  RMS_JSON: "config/PDS4_ALL_1F00.JSON",
  BOD_JSON: "config/PDS4_ALL_1F00.JSON",
  SPECT_JSON: "config/PDS4_ALL_1F00.JSON",
  WAV_JSON: "config/PDS4_ALL_1F00.JSON",
  MSN_JSON: "config/PDS4_ALL_1F00.JSON",
  MSN_SURFACE_JSON: "config/PDS4_ALL_1F00.JSON",
  IMG_SURFACE_JSON: "config/PDS4_ALL_1F00.JSON",
  NUC_JSON: "config/PDS4_ALL_1F00.JSON",
  SPECLIB_JSON: "config/PDS4_ALL_1F00.JSON",
  MSSS_CAM_MH_JSON: "config/PDS4_ALL_1F00.JSON",
  SP_JSON: "config/PDS4_ALL_1F00.JSON",
  PROC_JSON: "config/PDS4_ALL_1F00.JSON",
  SURVEY_JSON: "config/PDS4_ALL_1F00.JSON"

};

var filePaths_1A00 = {
  PDS_JSON: "config/PDS4_PDS_JSON_1A00_DOM.JSON",
  CART_JSON: "config/PDS4_CART_1A10.json",
  DISP_JSON: "config/PDS4_DISP_JSON_1A00_DOM.JSON",
  GEOM_JSON: "config/PDS4_GEOM_1A10.json",
  IMG_JSON: "config/PDS4_IMG_1A10.json",
  PART_JSON: "config/PDS4_PDS_JSON_1800.JSON",
  RMS_JSON: "config/PDS4_RINGS_JSON_1510_DOM.JSON",
  BOD_JSON: "config/PDS4_PDS_JSON_1800.JSON",
  SPECT_JSON: "config/PDS4_PDS_JSON_1800.JSON",
  WAV_JSON: "config/PDS4_PDS_JSON_1800.JSON",
  MSN_JSON: "config/PDS4_MSN_1A10.json",
  MSN_SURFACE_JSON: "config/PDS4_MSN_SURFACE_1A10.json",
  IMG_SURFACE_JSON: "config/PDS4_IMG_SURFACE_1A10.json",
  NUC_JSON: "config/PDS4_NUCSPEC_1A00.json"
};

var filePaths_1800 = {
    PDS_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    CART_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    DISP_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    GEOM_JSON: "config/PDS4_GEOM_1700_1401.json",
    IMG_JSON: "config/PDS4_IMG_1800.JSON",
    PART_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    RMS_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    BOD_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    SPECT_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    WAV_JSON: "config/PDS4_PDS_JSON_1800.JSON",
    MSN_JSON: "",
    MSN_SURFACE_JSON: "",
    IMG_SURFACE_JSON: "",
    NUC_JSON: ""


};



var filePaths_1700 = {
    PDS_JSON: "config/PDS4_PDS_JSON_1700.JSON",
    CART_JSON: "config/cart_1700.json",
    DISP_JSON: "config/geom_disp_rings_1700.json",
    GEOM_JSON: "config/PDS4_GEOM_1700_1401.json",
    IMG_JSON: "config/PDS4_IMG_1700.JSON",
    PART_JSON: "",
    RMS_JSON: "config/geom_disp_rings_1700.json",
    BOD_JSON: "",
    SPECT_JSON: "",
    WAV_JSON: "",
    MSN_JSON: "",
    MSN_SURFACE_JSON: "",
    IMG_SURFACE_JSON: "",
    NUC_JSON: ""
};

var filePaths_1600 = {
    PDS_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    CART_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    DISP_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    GEOM_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    IMG_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    PART_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    RMS_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    BOD_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    SPECT_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    WAV_JSON: "config/PDS4_PDS_JSON_1600.JSON",
    MSN_JSON: "",
    MSN_SURFACE_JSON: "",
    IMG_SURFACE_JSON: "",
    NUC_JSON: ""
}



/**
 * A dictionary of information specific to each namespace/dictionary.
 * Details TBD.
 */
var g_dictInfo_1600 = {
    pds: {
        ns: 'pds',
        name: 'Label Root',
        path: filePaths_1600["PDS_JSON"]
    },
    img: {
        ns: 'img',
        name: 'imaging',
        base_class: '0001_NASA_PDS_1.img.Imaging',
        path: filePaths_1600["IMG_JSON"]
    },
    geom: {
        ns: 'geom',
        name: 'geometry',
        base_class: '0001_NASA_PDS_1.geom.Geometry',
        path: filePaths_1600["GEOM_JSON"]
    },
    cart: {
        ns: 'cart',
        name: 'cartography',
        base_class: '0001_NASA_PDS_1.cart.Cartography',
        path: filePaths_1600["CART_JSON"]
    },
    disp: {
        ns: 'disp',
        name: 'display',
        base_class: '0001_NASA_PDS_1.disp.Display',
        path: filePaths_1600["DISP_JSON"]
    },
    rings: {
        ns: 'rings',
        name: 'rings',
        base_class: '0001_NASA_PDS_1.rings.Rings',
        path: filePaths_1600["RMS_JSON"]
    }

};


var g_dictInfo_1700 = {
    pds: {
        ns: 'pds',
        name: 'Label Root',
        path: filePaths_1700["PDS_JSON"]
    },
    img: {
        ns: 'img',
        name: 'imaging',
        base_class: '0001_NASA_PDS_1.img.Imaging',
        path: filePaths_1700["IMG_JSON"]
    },
    geom: {
        ns: 'geom',
        name: 'geometry',
        base_class: '0001_NASA_PDS_1.geom.Geometry',
        path: filePaths_1700["GEOM_JSON"]
    },
    cart: {
        ns: 'cart',
        name: 'cartography',
        base_class: '0001_NASA_PDS_1.cart.Cartography',
        path: filePaths_1700["CART_JSON"]
    },
    disp: {
        ns: 'disp',
        name: 'display',
        base_class: '0001_NASA_PDS_1.disp.Display',
        path: filePaths_1700["DISP_JSON"]
    },
    rings: {
        ns: 'rings',
        name: 'rings',
        base_class: '0001_NASA_PDS_1.rings.Rings',
        path: filePaths_1700["RMS_JSON"]
    }
};

var g_dictInfo_1800 = {
    pds: {
        ns: 'pds',
        name: 'Label Root',
        path: filePaths_1800["PDS_JSON"]
    },
    img: {
        ns: 'img',
        name: 'imaging',
        base_class: '0001_NASA_PDS_1.img.Imaging',
        path: filePaths_1800["IMG_JSON"]
    },
    geom: {
        ns: 'geom',
        name: 'geometry',
        base_class: '0001_NASA_PDS_1.geom.Geometry',
        path: filePaths_1800["GEOM_JSON"]
    },
    cart: {
        ns: 'cart',
        name: 'cartography',
        base_class: '0001_NASA_PDS_1.cart.Cartography',
        path: filePaths_1800["CART_JSON"]
    },
    disp: {
        ns: 'disp',
        name: 'display',
        base_class: '0001_NASA_PDS_1.disp.Display',
        path: filePaths_1800["DISP_JSON"]
    },
    rings: {
        ns: 'rings',
        name: 'rings',
        base_class: '0001_NASA_PDS_1.rings.Rings',
        path: filePaths_1800["RMS_JSON"]
    }
};

var g_dictInfo_1A00 = {
  pds: {
    ns: 'pds',
    name: 'Label Root',
    path: filePaths_1A00["PDS_JSON"]
  },
  img: {
    ns: 'img',
    name: 'imaging',
    base_class: '0001_NASA_PDS_1.img.Imaging',
    path: filePaths_1A00["IMG_JSON"]
  },
  geom: {
    ns: 'geom',
    name: 'geometry',
    base_class: '0001_NASA_PDS_1.geom.Geometry',
    path: filePaths_1A00["GEOM_JSON"]
  },
  cart: {
    ns: 'cart',
    name: 'cartography',
    base_class: '0001_NASA_PDS_1.cart.Cartography',
    path: filePaths_1A00["CART_JSON"]
  },
  disp: {
    ns: 'disp',
    name: 'display',
    base_class: '0001_NASA_PDS_1.disp.Display',
    path: filePaths_1A00["DISP_JSON"]
  },
  rings: {
    ns: 'rings',
    name: 'rings',
    base_class: '0001_NASA_PDS_1.rings.Rings',
    path: filePaths_1A00["RMS_JSON"]
  },

  nucspec: {
    ns: 'nucspec',
    name: 'nucspec',
    base_class: '0001_NASA_PDS_1.nucspec.GRNS_Observation_Properties',
    path: filePaths_1A00["NUC_JSON"]
  },

    msn: {
        ns: 'msn',
        name: 'mission',
        base_class: '0001_NASA_PDS_1.msn.Mission_Information',
        path: filePaths_1A00["MSN_JSON"]
    },
    msn_surface: {
        ns: 'msn_surface',
        name: 'mission_surface',
        base_class: '0001_NASA_PDS_1.msn_surface.Surface_Mission_Parameters',
        path: filePaths_1A00["MSN_SURFACE_JSON"]
    },
    img_surface: {
        ns: 'img_surface',
        name: 'imaging_surface',
        base_class: '0001_NASA_PDS_1.img_surface.Surface_Imaging_Parameters',
        path: filePaths_1A00["IMG_SURFACE_JSON"]
    }

};

var g_dictInfo_1F00 = {
  pds: {
    ns: 'pds',
    name: 'Label Root',
    path: filePaths_1F00["PDS_JSON"]
  },
  img: {
    ns: 'img',
    name: 'Imaging',
    base_class: '0001_NASA_PDS_1.img.Imaging',
    path: filePaths_1F00["IMG_JSON"]
  },
  geom: {
    ns: 'geom',
    name: 'Geometry',
    base_class: '0001_NASA_PDS_1.geom.Geometry',
    path: filePaths_1F00["GEOM_JSON"]
  },
  cart: {
    ns: 'cart',
    name: 'Cartography',
    base_class: '0001_NASA_PDS_1.cart.Cartography',
    path: filePaths_1F00["CART_JSON"]
  },
  disp: {
    ns: 'disp',
    name: 'Display_Settings',
    base_class: '0001_NASA_PDS_1.disp.Display_Settings',
    path: filePaths_1F00["DISP_JSON"]
  },
  rings: {
    ns: 'rings',
    name: 'Ring_Moon_Systems',
    base_class: '0001_NASA_PDS_1.rings.Ring_Moon_Systems',
    path: filePaths_1F00["RMS_JSON"]
  },

  nucspec: {
    ns: 'nucspec',
    name: 'NucSpec_Observation_Properties',
    base_class: '0001_NASA_PDS_1.nucspec.NucSpec_Observation_Properties',
    path: filePaths_1F00["NUC_JSON"]
  },

    msn: {
        ns: 'msn',
        name: 'Mission_Information',
        base_class: '0001_NASA_PDS_1.msn.Mission_Information',
        path: filePaths_1F00["MSN_JSON"]
    },
    msn_surface: {
        ns: 'msn_surface',
        name: 'Surface_Mission_Information',
        base_class: '0001_NASA_PDS_1.msn_surface.Surface_Mission_Information',
        path: filePaths_1F00["MSN_SURFACE_JSON"]
    },
    img_surface: {
        ns: 'img_surface',
        name: 'Surface_Imaging',
        base_class: '0001_NASA_PDS_1.img_surface.Surface_Imaging',
        path: filePaths_1F00["IMG_SURFACE_JSON"]
    },
    sp: {
        ns: 'sp',
        name: 'Spectral_Characteristics',
        base_class: '0001_NASA_PDS_1.sp.Spectral_Characteristics',
        path: filePaths_1F00["SP_JSON"]
    },
    msss_cam_mh: {
        ns: 'msss_cam_mh',
        name: 'MSSS_Camera_Mini_Header',
        base_class: '0001_NASA_PDS_1.msss_cam_mh.MSSS_Camera_Mini_Header',
        path: filePaths_1F00["MSSS_CAM_MH_JSON"]
    },
    speclib: {
        ns: 'speclib',
        name: 'Spectral_Library_Product',
        base_class: '0001_NASA_PDS_1.speclib.Spectral_Library_Product',
        path: filePaths_1F00["SPECLIB_JSON"]
    },
    proc: {
        ns: 'proc',
        name: 'Processing_Information',
        base_class: '0001_NASA_PDS_1.proc.Processing_Information',
        path: filePaths_1F00["PROC_JSON"]
    },
    survey: {
        ns: 'survey',
        name: 'Survey',
        base_class: '0001_NASA_PDS_1.survey.Survey',
        path: filePaths_1F00["SURVEY_JSON"]
    },



};


// ordering for core_schema_versions below is from last to first
// These values are now represented as Strings, rather than Integers
var schema_list_order = ["1F00", "1A00", "1800", "1700", "1600"];

// The keys are now represented as Strings, rather than Integers
var core_schema_versions = {
    "1F00": {
      name: "Version v1 (1.F.0.0) - December 23, 2020",
      filePaths: filePaths_1F00,
      g_dictInfo: g_dictInfo_1F00
    },
    "1A00": {
      name: "Version v1 (1.A.0.0) - February 20, 2018",
      filePaths: filePaths_1A00,
      g_dictInfo: g_dictInfo_1A00
    },
    "1800": {
        name: "Version v1 (1.8.0.0) - March 31, 2017",
        filePaths: filePaths_1800,
        g_dictInfo: g_dictInfo_1800
    },
    "1600": {
        name: "Version v1 (1.6.0.0) - March 31, 2016",
        filePaths: filePaths_1600,
        g_dictInfo: g_dictInfo_1600
    },
    "1700": {
        name: "Version v1 (1.7.0.0) - September 30, 2016",
        filePaths: filePaths_1700,
        g_dictInfo: g_dictInfo_1700
    },


};
/**
 * This object stores data related to the JSONs being referenced. It helps with
 * searching and quick reference of the JSON data.
 * @type {{refObj: {}, pds4Obj: {}, searchObj: {}, nodes: Array, currNS: string, namespaces: Array, currNode: string,
 * dataDictNodeInfo: Array}}
 */

var g_jsonData = {
    refObj: {},
    searchObj: {},
    nodes: {},
    namespaces: [],
    dataDictNodeInfo: []
};

/**
 * Maps namespace to specific node information.
 *
 */
// var

/** Maintains current state information
 */
var g_state = {
    currNS: "",         // maps to g_jsonData.namespaces[]
    nsIndex: 0,     // maps to g_jsonData.namespaces indexes
    loading: false
};

/**
 * This object contains information related to the PLAID wizard. Referencing this object
 * helps with control of the wizard.
 * @type {{currentStep: number, newStep: number, maxStep: number, numWarnings: number, mainSteps: Array}}
 */
var wizardData = {
    currentStep: 0,
    newStep: 0,
    maxStep: 0,
    numWarnings: 0,
    mainSteps: [],
    allSteps: [],
    stepPaths: []
};
/**
 * This data structure is for passing the top-level attributes of the user-created Mission-Specific data to the backend.
 * @type {missionName: String, stewardId: String, namespaceId: String, comment: String}
 */
var missionSpecificsHeader = {
     missionName: "Initial Mission Name",
     stewardId: "Initial Steward ID",
     namespaceId: "Initial Namespace ID",
     comment: "Initial Comment"
};

/**
 * This list is for storing the user created Mission Specifics content. It will
 * be stored in a JSON format correlating to the jqTree framework.
 * @type {Array}
 */
var missionSpecifics = [];
/**
 * This is initialized as a list but is formed as a JSON. It is for storing
 * data related to the user's progress throughout the PLAID wizard.
 * @type {Array}
 */
var progressData = [];
/**
 * This variable denotes whether PLAID is in a loading phase or not.
 * @type {boolean}
 */
var isLoading = false;
/**
 * This list is for storing the names of objects that incorrectly appear in the PDS4 JSON.
 * As the process of creating that JSON is solidified and the bugs are worked out, this list
 * will be deprecated.
 * @type {string[]}
 */
var invalidElementsInJSON = [
  "Conceptual_Object",
  "Physical_Object",
  "Digital_Object"
];

/**
 * This list is for storing the items that are recommended to be included in the label template
 *
 * Match any digit, escape the slash so it interprets it as an actual slash.
 * $ so it doesn't match:
 * e.g. "1/Observation_Area/4/Observing_System/2/Observing_System_Component/0/xxxx"
 *
 * Currently the list items will match the following data-paths:
 *  "1/Observation_Area/4/Observing_System/2/Observing_System_Component"
 *  "1/Observation_Area/5/Target_Identification/2/type"
 *
 * @type {string[]}
 */
var recommendedElementDataPaths= [
    "[0-9]\/Observation_Area\/[0-9]+/Observing_System\/[0-9]+\/Observing_System_Component$",
    "[0-9]\/Observation_Area\/[0-9]+/Target_Identification\/[0-9]+\/type$"
];


/**
 * This boolean is for storing whether we are currently in Basic Mode or Advanced Mode.
 * It will get re-initialized here when the page is reloaded.  Default to Basic Mode.
 * @type {boolean}
 */
var g_isBasicMode = true;

/**
 * This list is for specifying the data paths of elements that should only be shown in Advanced Mode,
 * as regular expressions.
 * @type {string[]}
 */
var advancedModeElementDataPaths = [
    // "0/Identification_Area/6/Citation_Information",
    //  Match any digit, escape the slash so it interprets it as an actual slash
    "[0-9]\/Identification_Area\/\[0-9]+\/Citation_Information$",
    // "1/Observation_Area/6/Mission_Area"
    "[0-9]\/Observation_Area\/[0-9]+\/Mission_Area$"
];

/**
 * This list is for specifying the data paths of elements that have been deprecated, and should be hidden.
 * @type {string[]}
 */
var deprecatedElementDataPaths = [
    //"0/Identification_Area/5/Alias_List",
    //  Match any digit, escape the slash so it interprets it as an actual slash
    "[0-9]\/Identification_Area\/[0-9]+\/Alias_List$",
    //"1/Observation_Area/2/Investigation_Area"
    //  Match any digit, escape the slash so it interprets it as an actual slash, $ so it doesn't match
    //  "1/Observation_Area/2/Investigation_Area/0/name"
    "[0-9]\/Observation_Area\/[0-9]+\/Investigation_Area$"
];

// XML DOM tree containing the imported XML file
let g_importedXmlDoc = null;
