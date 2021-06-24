import React, { Component } from 'react'
import { connect } from 'react-redux'
import PrintButton from '../../buttons/PrintButton'
import { Textbox } from 'react-inputs-validation';
import 'react-inputs-validation/lib/react-inputs-validation.min.css';

/**
 * renders ano create bars component
 * @extends Component
 */
class AnodizeMoveTagCreateBars extends Component {

    /**
     * init the class.
     * @param {object} props the props that we mapped below.
     */
    constructor(props) {
        super(props)
    }

    /**
     * init the class.
     * @param {int} ms time of delay in ms
     *
     * @returns {Promise}
     */
    delay(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    /**
     * handle key up event from # of bars to create input
     * @param {event} e triggering event
     */
    handleKeyUp (e){
        //create short delay to allow multiple length integer to be entered before triggering
        this.delay(500).then(() => {
            let targetElement = e.target
            e.preventDefault()

            if (targetElement.id === 'itemsPerBar') {
                let qty = parseInt(document.querySelector('.totalQuantity').innerText)
                let itemsPerBar = parseInt(document.querySelector('#itemsPerBar').value)
                let bars = Math.ceil(qty/itemsPerBar)
                let barsText = "This will print " + bars + " bar codes."
                document.querySelector('#totalBars').innerText = barsText
                this.props.dispatch({type: "ANO_NUMBER_BAR_CODES", data: bars});
            }
        })
    }

    render(){
        let data = this.props.data.record
        let numberOfBars = this.props.data.number_of_barcodes

        const renderPrintComponent = () => {
            if (numberOfBars > 0) {
                return <PrintButton />
            } else {
                return ""
            }
        }

        return(
            <div className="col-12 no-padding-margin">
                <div className="col-12 no-padding-margin">
                    <h3>Customer: <br />{ data.mmt_cusnam }</h3>
                    <table className="table" id="anoTagTable">
                        <thead className="head-light">
                            <tr>
                                <th scope="col">
                                    Tag No
                                </th>
                                <th scope="col">
                                    Lot No
                                </th>
                                <th scope="col">
                                    Die
                                </th>
                                <th scope="col">
                                    Length
                                </th>
                                <th scope="col">
                                    Qty
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">
                                   { data.mmt_tagno }
                                </th>
                                <td>
                                    { data.mmt_lotno }
                                </td>
                                <td>
                                    { data.mmt_die }
                                </td>
                                <td>
                                    { data.mmt_length }
                                </td>
                                <td className="totalQuantity">
                                    { data.mmt_qty }
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <form method="POST" name="anodizeSelectBars" id="anodizeSelectBars" className="col-12">
                    <div className="form-group container">
                        <div className="col-12">
                            <Textbox
                                attributesInput={{
                                    id: "itemsPerBar",
                                    name: "itemsPerBar",
                                    type: "number",
                                    placeholder: "items per bar?",
                                    autoFocus: true
                                }}
                                disabled={false} // Optional.[Bool].Default: false.
                                validate={true} // Optional.[Bool].Default: false. If you have a submit button and trying to validate all the inputs of your form at onece, toggle it to true, then it will validate the field and pass the result via the "validationCallback" you provide.
                                validationCallback={res => {}} // Optional.[Func].Default: none. Return the validation result.
                                classNameInput="form-control col-12" // Optional.[String].Default: "".
                                onKeyUp={this.handleKeyUp.bind(this)} // Required.[Func].Default: () => {}. Will return the value.
                                onBlur={e => {}} // Optional.[Func].Default: none. In order to validate the value on blur, you MUST provide a function, even if it is an empty function. Missing this, the validation on blur will not work.
                                validationOption={{
                                    name: "# of Bars", // Optional.[String].Default: "". To display in the Error message. i.e Please enter your ${name}.
                                    check: true, // Optional.[Bool].Default: true. To determin if you need to validate.
                                    required: true, // Optional.[Bool].Default: true. To determin if it is a required field.
                                    min: 1, // Optional.[Number].Default: 0. Validation of min length when validationOption['type'] is string, min amount when validationOption['type'] is number.
                                    max: 3, // Optional.[Number].Default: 0. Validation of max length when validationOption['type'] is string, max amount when validationOption['type'] is number.
                                }}
                            />
                            <div id="totalBars"></div>
                            { renderPrintComponent() }
                        </div>
                    </div>
                    <input type="hidden" name="_token" value={this.props.csrf} />
                </form>
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

export default connect(mapStateToProps)(AnodizeMoveTagCreateBars)
