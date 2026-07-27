// adjust block end margin for the footer based on the height of the CTA block adjusts the positioning of Simple popup blocks pinned to the bottom to accomodate sticky CTAs on mobile. CTAs are stickied at the bottom and will be covered by the Simple popup block
const cta = document.getElementById('block-madrone-globalcalltoaction');
const footer = document.querySelector('.madrone-copyright');
const largeBreakpoint = 992;

const bottomSimplePopUp = document.getElementsByClassName('spb_bottom_bar');

function updateMargins() {
    if (!cta || !footer) {
        console.log("I didn't find the CTA or footer");
        return;
    }

    if (document.documentElement.clientWidth < largeBreakpoint) {
        footer.style.marginBlockEnd = `calc(${cta.offsetHeight}px + 1rem)`;

        for (let i = 0; i < bottomSimplePopUp.length; i++) {
            bottomSimplePopUp[i].style.position = `relative`;
        }

    } else {
        footer.style.marginBlockEnd = '';
        for (let i = 0; i < bottomSimplePopUp.length; i++) {
            bottomSimplePopUp[i].style.position = `fixed`;
        }
    }
}

if (cta && footer) {
    updateMargins();

    window.addEventListener('resize', updateMargins);

    const observer = new ResizeObserver(updateMargins);
    observer.observe(cta);
}


