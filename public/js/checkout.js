let STRIPE_PUBLISHABLE_KEY = document.currentScript.getAttribute('STRIPE_PUBLISHABLE_KEY');

document.addEventListener('DOMContentLoaded', function() {


    const stripe = Stripe(STRIPE_PUBLISHABLE_KEY);

    const pay_on_spot = 'pay_on_spot';
    const paymentEle = 'paymentElement';
    let elements;
    const paymentFrm = "shopping-cart-frm";

    const clientSecretParam = new URLSearchParams(window.location.search).get("payment_intent_client_secret");

    if (!clientSecretParam && getUIOrderType() !== pay_on_spot) {
        initialize();
    } else {
        showCheckoutBtn();
    }

    checkStatus();

    async function initialize() {

        $.ajax({
            type: "GET",
            url: "/payment-init",
            dataType : 'json',
            data : {
                request_type : 'create_payment_intent'
            },
            success: function(result){
                const appearance = {
                    //theme: 'night',
                    rules: {
                        '.Label': {
                            fontSize: '0' // for hiding label elements for card detail
                        }
                    },
                    // for dark theme.. default is white theme
                    //    variables: {
                    //     colorPrimary: '#212529',
                    //     colorBackground: '#212529',
                    //     colorText: '#ffffff',
                    //     colorDanger: '#df1b41',
                    // }
                };
                let clientSecret = result.api.clientSecret;
                elements = stripe.elements({ clientSecret , appearance });

                const paymentElement = elements.create("payment");
                paymentElement.mount("#"+paymentEle);

                payment_intent_id = result.api.id;

                showCheckoutBtn(); // showing checkout button after card payment element is being loaded
            }
        });
    }

    $('#'+paymentFrm).validate({
        rules: {
            first_name: { required: true },
            last_name: { required: true },
            email: { required: true, email: true },
            phone: { required: true, number: true, minlength: 10, maxlength: 10 },
            address: { required: true },
            post_code: { required: true, number: true },
            city_id: { required: true },
        },
        messages: {
            first_name: { required: "First Name required" },
            last_name: { required: "Last Name required" },
            address: { required: "Address required" },
            post_code: { required: "Post Code required", number: "Post Code must be number" },
            email: { required: "Email required", email: "Email must be valid" },
            phone: { required: "Phone required", number: "Phone must be number",
                minlength: "phone number must be min 10 number",
                maxlength: "phone number must be max 10 number"
            }
        },
        submitHandler: function(form) {
            setLoading(true);
            if (getUIOrderType() !== pay_on_spot) {
                $.ajax({
                    type: "GET",
                    url: "/payment-init",
                    dataType: 'json',
                    data: {
                        request_type: 'create_customer',
                        payment_intent_id: payment_intent_id,
                        first_name: document.getElementById("first_name").value,
                        last_name: document.getElementById("last_name").value,
                        phone: document.getElementById("phone").value,
                        address: document.getElementById("address").value,
                        post_code: document.getElementById("post_code").value,
                        email: document.getElementById("email").value,
                        notes: document.getElementById("notes").value,
                        shipping_cost: document.getElementById("shipping_cost").value,
                        order_type: getUIOrderType(),
                    },
                    success: function(result) {
                        customer_id = result.api.customer_id;
                        order_id = result.api.order_id;
                        handleSubmit();
                    }
                });
            } else {
                $('#' + paymentFrm).submit();
            }
            return false;
        }
    });

    async function handleSubmit(e) {
        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: window.location.href + '?order_id=' + order_id + '&customer_id=' + customer_id,
            },
        });
        if (error.type === "card_error" || error.type === "validation_error") {
            showMessage(error.message);
        } else {
            showMessage("An unexpected error occured.");
            $("#" + paymentEle).html('');
            elements = null;
            initialize();
        }
        setLoading(false);
    }

    async function checkStatus() {
        const clientSecret = new URLSearchParams(window.location.search).get("payment_intent_client_secret");
        const customerID = new URLSearchParams(window.location.search).get("customer_id");
        const orderID = new URLSearchParams(window.location.search).get("order_id");
        if (!clientSecret) {
            return;
        }
        const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);
        if (paymentIntent) {
            switch (paymentIntent.status) {
                case "succeeded":
                    $.ajax({
                        type: "GET",
                        url: "/payment-init",
                        dataType: 'json',
                        data: {
                            request_type: 'payment_insert',
                            payment_intent: paymentIntent,
                            customer_id: customerID,
                            order_id: orderID,
                        },
                        success: function(result) {
                            if (result.transactionID) {
                                window.location.href = '/orders/confirmed/' + orderID;
                            } else {
                                showMessage(result.error);
                            }
                        }
                    });
                    break;
                case "processing":
                    showMessage("Your payment is processing.");
                    break;
                case "requires_payment_method":
                    showMessage("Your payment was not successful, please try again.");
                    break;
                default:
                    showMessage("Something went wrong.");
                    break;
            }
        } else {
            showMessage("Something went wrong.");
        }
    }


});

function showMessage(messageText) {
    const messageContainer = document.querySelector("#paymentResponse");
    messageContainer.classList.remove("hidden");
    messageContainer.classList.add("text-danger");
    messageContainer.textContent = messageText;
    setTimeout(function () {
        messageContainer.classList.add("hidden");
        messageContainer.classList.remove("text-danger");
        messageContainer.textContent = "";
    }, 5000);
}

function setLoading(isLoading) {
    if (isLoading) {
        document.querySelector("#checkout-btn").disabled = true;
    } else {
        document.querySelector("#checkout-btn").disabled = false;
    }
}

function showCheckoutBtn() {
    $("#checkout-btn").show();
}
function hideCheckoutBtn() {
    $("#checkout-btn").show();
}

$('input[name="order_type"]').on('click', function(event) {
    if ($(this).val() === pay_on_spot) {
        $("#" + paymentEle).html('');
        elements = null;
    } else {
        initialize();
    }
});

function getUIOrderType(){
    const selectedRadio = document.querySelector('input[name="order_type"]:checked');
    return selectedRadio ? selectedRadio.value : null;
}
