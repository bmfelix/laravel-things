import React, { Component } from 'react'
import { connect } from 'react-redux'
import AnoMoveTag from '../../../apis/AnoMoveTag'
import ErrorModal from '../../modal/ErrorModal'
import LoadTagModal from '../../modal/LoadTagModal'
import UndeleteModal from '../../modal/UndeleteModal'
import { Textbox } from 'react-inputs-validation';
import 'react-inputs-validation/lib/react-inputs-validation.min.css';

/**
 * renders ano tag scan page
 * @extends Component
 */
class AnodizeMoveTagScan extends Component {

    /**
     * init the class.
     * @param {object} props the props that we mapped below.
     */
    constructor(props) {
        super(props)
    }

    /**
     * handle key up event from scan input
     * @param {event} e triggering event
     */
    handleKeyUp (e){
        let targetElement = e.target
        e.preventDefault()

        if (targetElement.id === 'movetag') {

            const barcode = targetElement.value.split('-')
            const tag = barcode[0]
            let step = null;
            let bar =  null;

            if (barcode[1]) {
                step = barcode[1]
            }

            if (barcode[2]) {
                bar = barcode[2]
            }

            if (
                tag.length >= 7 &&
                tag.length <= 20 &&
                step === null &&
                bar === null
            ) {
                this.processRequest()
            } else if (
                tag.length >= 6 &&
                tag.length <= 20 &&
                step !== null &&
                bar !== null
            ) {
                this.processStep(tag, step, bar)
            }
        }
    }

    /**
     * goes and gets data from API, then calls the appropriate dispatch event
     */
    processRequest(){
        AnoMoveTag.process().then(response => {
            let data = response.data
            if (data.code != 200) {
                this.props.dispatch({type: "ANO_TAG_SCANNED_FAILURE", error: data.message})
            } else {
                this.props.dispatch({type: "ANO_TAG_SCANNED_SUCCESS", data: data.data});
            }
        })
        .catch(error=>{
            console.log(error)
        })
    }

    /**
     * goes and gets a particular step of data from API, then calls the appropriate dispatch event
     */
    processStep(tag, step, bar){
        AnoMoveTag.loadStep(tag, step, bar).then(response => {
            let data = response.data
            if (data.code != 200) {
                this.props.dispatch({type: "ANO_TAG_LOAD_STEP_FAILURE", error: data.message})
            } else {
                this.props.dispatch({type: "ANO_TAG_LOAD_STEP_SUCCESS", movetag: data.movetag, bar: data.bar});
            }
        })
        .catch(error=>{
            console.log(error)
        })
    }

    render(){
        return(
            <div>
                <ErrorModal showError={this.props.data.showError}/>
                <LoadTagModal showLoadTag={this.props.data.showLoadTag}/>
                <UndeleteModal showLoadTag={this.props.data.showUndelete}/>
                <h3>Scan move tag...</h3>
                <div className="col-12">
                    <form method="POST" name="anodizeMoveTagScan" id="anodizeMoveTagScan" action="/anodize/movetag" className="col-12">
                        <input type="hidden" name="_token" value={this.props.csrf} />
                        <div className="form-group container">
                            <div className="col-12">
                                <Textbox
                                    attributesInput={{
                                        id: "movetag",
                                        name: "movetag",
                                        type: "text",
                                        placeholder: "Scan Move Tag",
                                        autoFocus: true
                                    }}
                                    disabled={false} // Optional.[Bool].Default: false.
                                    validate={true} // Optional.[Bool].Default: false. If you have a submit button and trying to validate all the inputs of your form at onece, toggle it to true, then it will validate the field and pass the result via the "validationCallback" you provide.
                                    validationCallback={res => {}} // Optional.[Func].Default: none. Return the validation result.
                                    classNameInput="form-control col-12" // Optional.[String].Default: "".
                                    onKeyUp={this.handleKeyUp.bind(this)} // Required.[Func].Default: () => {}. Will return the value.
                                    onBlur={e => {}} // Optional.[Func].Default: none. In order to validate the value on blur, you MUST provide a function, even if it is an empty function. Missing this, the validation on blur will not work.
                                    validationOption={{
                                        name: "Move Tag", // Optional.[String].Default: "". To display in the Error message. i.e Please enter your ${name}.
                                        check: true, // Optional.[Bool].Default: true. To determin if you need to validate.
                                        required: true, // Optional.[Bool].Default: true. To determin if it is a required field.
                                        min: 6, // Optional.[Number].Default: 0. Validation of min length when validationOption['type'] is string, min amount when validationOption['type'] is number.
                                        max: 20, // Optional.[Number].Default: 0. Validation of max length when validationOption['type'] is string, max amount when validationOption['type'] is number.
                                    }}
                                />
                            </div>
                        </div>
                    </form>
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

export default connect(mapStateToProps)(AnodizeMoveTagScan)
