import React, { Component } from "react";
import { connect } from 'react-redux'
import AnoLoadTag from "../buttons/AnoLoadTag";

//defined inline style for modal window
const style = {
    fontSize: '18px',
    fontWeight: 'bold'
};

/**
 * renders error modal window
 * @extends Component
 */
class ErrorModal extends Component {

    /**
     * init the error modal
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
     * handle loading of tag, if not loaded into department
     */
    handleLoadTag(tagno) {
        this.props.dispatch({type: "ANO_SHOW_LOAD_TAG_MODAL", data: tagno})
    }

    /**
     * handle restoring a deleted move tag
     */
    handleUndeleteTag(tagno) {
        this.props.dispatch({type: "ANO_SHOW_UNDELETE_MODAL", data: tagno})
    }

    render() {
        //ignore loading this modal, unless we have set the show error flag in redux
        if(!this.props.showError){
            return null
        }

        let notLoaded = false
        let button = null

        //Load the correct button based on error message received from the API
        if (this.props.data.error_message.includes('Tag is not loaded into this department')) {
            notLoaded = true
            let expression = /\d+/;
            let matches = this.props.data.error_message.match(expression)
            let tagNo = matches[0]
            button = <AnoLoadTag onClick={this.handleLoadTag.bind(this, tagNo)} />
        } else if (this.props.data.error_message.includes('This tag was deleted')) {
            notLoaded = true
            let expression = /TagNo: (\d+)/;
            let matches = this.props.data.error_message.match(expression)
            let tagNo = matches[1]
            button = <AnoLoadTag onClick={this.handleUndeleteTag.bind(this, tagNo)} />
        }

        return (
            <div className="modal" id="status_modal">
                <div className="modal-content">
                    <div className="modal-dialog text-danger" style={style}>
                        Error: {this.props.data.error_message}
                    </div>
                    <div className="closeModal col-12">
                        <button
                            id="closeModal"
                            className="btn btn-primary col-6"
                            onClick={this.handleClose.bind(this)}
                        >
                            Close
                        </button>
                        { button }
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

export default connect(mapStateToProps)(ErrorModal)
