import { featuresHomeArr, review, roomDataArr, whyGuestLoveUsArr,gardenDetails } from "../Assets/data.js";
window.addEventListener("DOMContentLoaded", () => {
    const godown = document.querySelector("#godown");
    const section2 = document.querySelector("#section2");
    const navContainer = document.querySelector("#nav-cont");
    const userName = document.querySelector("#userName");
    if (godown && section2 && navContainer) {
        console.log(userName);
        if (window.scrollY < 100)
            window.addEventListener("scroll", () => {
                if (window.scrollY > 50) {
                    navContainer.classList.remove("text-white");
                    navContainer.classList.add("text-black");
                    navContainer.classList.add("bg-white");
                    if (userName) {
                        userName.classList.remove("text-white");
                        userName.classList.add("text-black");
                    }
                } else {
                    navContainer.classList.remove("text-black");
                    navContainer.classList.add("text-white");
                    navContainer.classList.remove("bg-white");
                    if (userName) {
                        userName.classList.remove("text-black");
                        userName.classList.add("text-white");
                    }
                }
            });

        godown.addEventListener("click", () => {
            section2.scrollIntoView({
                behavior: "smooth",
            });
        });
    }
    const whyGuestLoveUs = document.querySelector("#whyGuestLoveUs");

    let htmlElement = "";
    if (whyGuestLoveUs) {
        whyGuestLoveUsArr.map((el) => {
            htmlElement += `<div class="p-8 bg-white border border-gray-200 hover:shadow-lg rounded-lg group transition-all duration-200">
                        <span class="p-4 rounded-lg scale-110  bg-[#E0E4EB] group-hover:bg-[#193366] transition-all duration-200"
                            ><i class="group-hover:text-white  ${el.icon} transition-all duration-200"></i
                        ></span>
                        <div class="py-4 mt-5">
                            <h1 class="text-xl md:text-2xl text-black/90  mb-3 text-playfair font-semibold">${el.title}</h1>
                            <p class="text-black/50">${el.desc}</p>
                        </div>
                    </div>`;
        });
        // console.log(htmlElement);
        whyGuestLoveUs.innerHTML = htmlElement;
    }

    const featuresHome = document.querySelector("#featuresHome");

    let featuresHomeElement = "";
    if (featuresHome) {
        featuresHomeArr.map((el) => {
            featuresHomeElement += `<div class="flex gap-2 ">
                                    <span class="p-1 w-8 h-8  inline-flex justify-center items-center bg-[#E3E6EB] text-[#193366] rounded-full"
                                        ><svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="lucide lucide-check w-4 h-4 text-primary"
                                        >
                                            <path d="M20 6 9 17l-5-5"></path></svg
                                    ></span>
                                    <p class="text-black/75">${el}</p>
                                </div>`;
        });
        featuresHome.innerHTML = featuresHomeElement;
    }

    const roomContainer = document.querySelector("#roomContainer");
    let roomCard = "";

    if (roomContainer) {
        roomDataArr.map((el,i) => {
            roomCard += ` <div class="rounded-xl pb-2 relative bg-white shadow-sm group hover:shadow-xl transition-all duration-400">
                        <div class="text-white absolute z-1 py-px rounded-full px-4 top-3 right-3 bg-[#193366] ">Rs.${el.price}00/night</div>
                               <div class="rounded-t-lg w-full object-cover h-55 overflow-hidden ">

                            <img src="${el.image}" alt=""   class="w-full h-60 group-hover:scale-110 transition-all duration-500 object-cover"/>
                        </div>
                        <div class="p-4 flex flex-col">
                            <span class="flex items-center gap-2 mb-2">
                                <i class="fa-solid fa-user-group text-black/60"></i>
                                <p class="text-sm text-black/70 font-medium">Up to ${el.noOfGuest} guests</p>
                            </span>
                            <h1 class="text-playfair font-semibold text-xl text-black/80 mb-2">${el.label}</h1>
                            <p class="text-black/60 mb-4 text-sm">${el.description}</p>
                            <div class="flex flex-wrap mb-6 gap-2">
                                <div class="flex items-center gap-2 py-1 bg-[#F7F4ED] px-2 rounded-full">
                                    <i class="fa-solid fa-check text-xs"></i>
                                    <h6 class="text-sm text-black/60 font-medium">Free Wifi</h6>
                                </div>
                                <div class="flex items-center gap-2 py-1 bg-[#F7F4ED] px-2 rounded-full">
                                    <i class="fa-solid fa-check text-xs"></i>
                                    <h6 class="text-sm text-black/60 font-medium">Air Conditioning</h6>
                                </div>
                                <div class="flex items-center gap-2 py-1 bg-[#F7F4ED] px-2 rounded-full">
                                     <i class="fa-solid fa-check text-xs"></i>
                                    <h6 class="text-sm text-black/60 font-medium">Private Bathroom</h6>
                                </div>
                                <div class="flex items-center gap-2 py-1 bg-[#F7F4ED] px-2 rounded-full">
                                      <i class="fa-solid fa-check text-xs"></i>
                                    <h6 class="text-sm text-black/60 font-medium">Free Wifi</h6>
                                </div>
                            </div>
                            <a href="checkout.php?id=${i+1}" class="bg-[#193366] transition-all duration-200 cursor-pointer text-white text-center rounded-full py-2 px-4 hover:bg-[#304775]">Book This Room</a>
                        </div>
                    </div>`;
        });
        roomContainer.innerHTML = roomCard;
    }

    const guestMessageContainer = document.querySelector("#guestMessage");

    let activeIndex = 0;
    let guestMessage = "";
    if (guestMessageContainer) {
        window.IncrementActiveIndex = () => {
            activeIndex < review.length - 1 ? activeIndex++ : (activeIndex = 0);
            DisplayMessage(review[activeIndex]);
            MakeActiveSlider(activeIndex);
        };

        window.DecrementActiveIndex = () => {
            activeIndex > 0 ? activeIndex-- : (activeIndex = review.length - 1);
            DisplayMessage(review[activeIndex]);
            MakeActiveSlider(activeIndex);
        };

        window.DisplayMessage = (el) => {
            guestMessage = `<p class="text-xl text-white/90 mx-auto max-w-2xl">
                            "${el.message}"
                        </p>
                        <div class="text-center text-white/80 mt-8">
                            <i class="fa-regular fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <div class="mt-8 flex gap-4 justify-center items-center">
                            <img class="border-4 border-[#193366] w-18 h-18 object-cover overflow-hidden rounded-full" src="${el.image}" alt="" />
                            <div class="text-left">
                                <h1 class="text-lg">${el.name}</h1>
                                <h4 class="text-white/70 text-base">${el.address}</h4>
                            </div>
                        </div>
                        <div class="flex gap-4 justify-center mt-10" id="sliderCOntainer">
                            <span class="flex justify-center items-center inset-0 p-3 cursor-pointer border hover:bg-[#313846] transition-colors duration-200 rounded-full border-gray-500" onclick='DecrementActiveIndex()'
                                ><svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-chevron-left w-5 h-5"
                                >
                                    <path d="m15 18-6-6 6-6"></path></svg
                            ></span>
                            <div class="flex gap-2 justify-center items-center">
                                <span class="w-3 h-3 transition-all duration-200 bg-[#5E636E] rounded-full imgSlider" ></span>
                                <span class="w-3 h-3 transition-all duration-200 bg-[#5E636E] rounded-full imgSlider" ></span>
                                <span class="w-3 h-3 transition-all duration-200 bg-[#5E636E] rounded-full imgSlider" ></span>
                                <span class="w-3 h-3 transition-all duration-200 bg-[#5E636E] rounded-full imgSlider" ></span>
                            </div>
                            <span class="flex justify-center items-center inset-0 p-3 cursor-pointer hover:bg-[#313846] transition-colors duration-200 border rounded-full border-gray-500" onclick='IncrementActiveIndex()'
                                ><svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-chevron-right w-5 h-5"
                                >
                                    <path d="m9 18 6-6-6-6"></path></svg
                            ></span>
                        </div>`;
            guestMessageContainer.innerHTML = guestMessage;
        };
        const sliderCOntainer = document.querySelector("#sliderCOntainer");

        DisplayMessage(review[0]);
        window.MakeActiveSlider = (index) => {
            setTimeout(() => {
                const imgSlider = Array.from(document.querySelectorAll(".imgSlider"));
                imgSlider.map((el) => {
                    el.classList.replace("bg-[#193366]", "bg-[#5E636E]");
                    el.classList.remove("w-9");
                    el.classList.add("w-3");
                    // console.log(el);
                });
                imgSlider[index].classList.remove("w-3");
                imgSlider[index].classList.add("w-9");
                imgSlider[index].classList.replace("bg-[#5E636E]", "bg-[#193366]");
            }, 1);
        };
        // imgSlider[activeIndex].classList.add("w-9");
        MakeActiveSlider(0);
    }


    const gardenHeaderData=document.querySelector("#garden-header-data");
    if(gardenHeaderData){
        let  GardeninnerHtml="";
        gardenDetails.map((el,i)=>{
            GardeninnerHtml+=`<span
                        class="border bg-white border-gray-200 py-4 gap-4 px-6 rounded-lg flex justify-center items-center">
                        <i class="${el.icon}"></i>
                        <span>
                            <h2 class="text-lg text-black/80 font-medium">${el.title}</h2>
                            <p class="text-xs text-black/60">${el.description}</p>
                        </span>
                    </span>`;
        })
          
        gardenHeaderData.innerHTML=GardeninnerHtml;
    }

});
