import React, { Component } from "react";
import { connect } from 'react-redux'
import Barcode from 'react-barcode'
import axios from "axios";

/**
 * renders generic anodize modal window
 * @extends Component
 */
class AnoModal extends Component {
    /**
     * init the ano modal
     *
     * @param   {object}  props  the props that we mapped below.
     */
    constructor(props) {
        super(props)
    }

    /**
     * handle close event of modal window
     */
    handleClose() {
        this.props.dispatch({type: "ANO_CLOSE_MODAL"});
    }

    /**
     * send barcode to laravel API that sends the print command to printer
     */
    async handlePrint() {
        let barcodes = this.props.data.barcodes
        for (let bar of barcodes) {
            await axios.get('/api/anodize/print/' + bar)
                .then(response => {
                    console.debug(response.data)
                })
                .catch(error=>{
                    console.log(error)
                })
        }
    }

    render() {
        if(!this.props.show){
            return null;
        }

        let barcodes = this.props.data.barcodes

        return (
            <div className="modal" id="status_modal">
                <div className="modal-content">
                    <div className="modal-dialog">
                        {
                            //map barcodes to Barcode component and return the JSX to be rendered
                            barcodes.map((barcode, index) => {
                                return <div key={index} className="pagebreak"><Barcode value={barcode} renderer='img' height={20}/></div>
                            })
                        }
                    </div>
                    <div className="closeModal col-12">
                        <button
                            id="closeModal"
                            className="btn btn-primary col-6"
                            onClick={this.handleClose.bind(this)}
                        >
                            Close
                        </button>
                        <button
                            id="printModal"
                            className="btn btn-danger col-6"
                            onClick={this.handlePrint.bind(this)}
                        >
                            Print
                        </button>
                    </div>
                </div>
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

export default connect(mapStateToProps)(AnoModal)
