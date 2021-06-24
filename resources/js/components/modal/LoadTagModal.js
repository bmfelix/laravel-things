import React, { Component } from "react";
import { connect } from 'react-redux'
import AnoMoveTag from "../../apis/AnoMoveTag";

//defined inline style for modal window
const style = {
    fontSize: '18px',
    fontWeight: 'bold'
};

/**
 * renders error modal window
 * @extends Component
 */
class LoadTagModal extends Component {

    /**
     * init the load tag modal
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
        this.props.dispatch({type: "ANO_CLOSE_MODAL"})
    }

     /**
     * handle yes button click to load tag into dept
     */
    handleYes() {
        let tagno = this.props.data.loadTagNo
        AnoMoveTag.loadTag(tagno).then(response => {
            if (response.data.message.includes('was loaded into Anodize')) {
                AnoMoveTag.process().then(response => {
                    let data = response.data
                    if (data.code != 200) {
                        this.props.dispatch({type: "ANO_TAG_SCANNED_FAILURE", error: data.message})
                    } else {
                        this.props.dispatch({type: "ANO_TAG_SCANNED_SUCCESS", data: data.data});
                    }
                })
            }
        })
    }

    render() {
        if(!this.props.showLoadTag){
            return null
        }

        return (
            <div className="modal" id="status_modal">
                <div className="modal-content">
                    <div className="modal-dialog text-danger" style={style}>
                        Confirm: You want to load move tag {this.props.data.loadTagNo} into the anodize department.
                    </div>
                    <div className="closeModal col-12">
                        <button
                            id="loadTagYesModal"
                            className="btn btn-success col-6"
                            onClick={this.handleYes.bind(this)}
                        >
                            Yes
                        </button>
                        <button
                            id="loadTagNoModal"
                            className="btn btn-danger col-6"
                            onClick={this.handleClose.bind(this)}
                        >
                            No
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

export default connect(mapStateToProps)(LoadTagModal)
