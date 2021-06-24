import axios from "axios";

/**
 * This constant stores functions we need to talk to the laravel API
 */

const AnoMoveTag = {

    //take scanned input and go get and return the data
    process: () => {
        let form = document.querySelector('#anodizeMoveTagScan')
        let formData = new FormData(form)
        return axios.post('/api/anodize/movetag', formData)
    },

    //load movetag into Anodize Move Tag table
    loadTag: (tagno) => {
        return axios.get('/api/anodize/movetag/load/' + tagno)
    },

    //if tag was deleted, this will re-enable it
    undeleteTag: (tagno) => {
        return axios.get('/api/anodize/movetag/load/' + tagno + '/true')
    },

    //create bar in the ano bars table
    createBar: (tag, step, number) => {
        let form = {tag: tag, step: step, number: number}
        return axios.post('/api/anodize/movetag/create/bars', form)
    },

    //load specific next step
    loadStep: (tag, step, bar) => {
        let form = {tag: tag, step: step, bar: bar}
        return axios.post('/api/anodize/movetag/load/step', form)
    },

    //save tank info to the database
    saveTankInfo: () => {
        let form = document.querySelector('#anodizeTankInfo')
        let formData = new FormData(form)
        return axios.post('/api/anodize/movetag/save/tank/info', formData)
    }
};

export default AnoMoveTag;
