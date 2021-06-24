import React, { Component } from 'react'
import { connect } from 'react-redux'
import AnoMoveTag from '../../apis/AnoMoveTag'
import _ from "lodash";

/**
 * renders print button, to print the barcodes
 * @extends Component
 */
class PrintButton extends Component {
    /**
     * init the class.
     * @param {object} props the props that we mapped below.
     */
    constructor(props) {
        super(props)
    }

    /**
     * handles on click event of print button
     *
     * @param   {Event}  e  event that triggered
     */
    async handleOnClick(e) {
        e.preventDefault()
        let numberOfBarcodes = this.props.data.number_of_barcodes
        let step = this.props.data.step
        let moveTag = this.props.data.record.mmt_tagno

        let barcodes = []

        //build array for use in for loop using lodash
        const codeArray = _.range(1, (numberOfBarcodes + 1))

        for (let bar of codeArray) {
            let stepNo = step + 2

            //create bar in database and return the response
            let barResp = await AnoMoveTag.createBar(moveTag, stepNo, bar).then(response => {
                if (
                    response.data.message.includes('Bar created') ||
                    response.data.message.includes('Bar exists')
                ) {
                    return response.data.barcode
                }
            })

            /*
                push barcode into array
                we will send this to our dispatch event
            */
            barcodes.push(barResp)
        }

        //update redux state with our dispath event
        this.props.dispatch({type: "ANO_PRINT_BAR_CODES", data: barcodes})
    }

    render() {
        return (
            <div>
                <button onClick={this.handleOnClick.bind(this)} className="btn btn-success col-12">PRINT</button>
            </div>
        )
    }
}

/**
 * Map our state to props
 * making them available on this.props
 *
 * @param {object} state  //our state from redux
 *
 * @return  {object} //our state mapped to props
 */
function mapStateToProps(state) {
    return {
        data: state.rootReducer.anoScanReducer,
        csrf: state.rootReducer.mainReducer.csrf
    }
}

export default connect(mapStateToProps)(PrintButton)
