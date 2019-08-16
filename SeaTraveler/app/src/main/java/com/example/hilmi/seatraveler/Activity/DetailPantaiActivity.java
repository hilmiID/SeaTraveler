package com.example.hilmi.seatraveler.Activity;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.Uri;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.support.v4.app.FragmentActivity;
import android.util.Log;
import android.view.View;
import android.widget.ImageView;
import android.widget.RatingBar;
import android.widget.TextView;
import android.widget.Toast;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;


import com.example.hilmi.seatraveler.R;
import com.squareup.picasso.Picasso;

public class DetailPantaiActivity extends AppCompatActivity implements OnMapReadyCallback {

    private ImageView showRoute, gambarPantai;
    private TextView namaPantai, deskripsiPantai, jarakPantai, alamatPantai, fasilitasPantai, transportasiPantai, htmPantai;
    private RatingBar ratingPantai;
    private String latitude, longitude, valueJarak, valueRating, valueFasilitas, valueTransportasi, valueNamaPantai, myLatitude, myLongitude, valueBiayaMasuk, image, deskripsi, alamat;
    private GoogleMap mMap;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_detail_pantai);
        // Obtain the SupportMapFragment and get notified when the map is ready to be used.
        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        mapFragment.getMapAsync(this);

        //initialize imageview and textview
        showRoute = findViewById(R.id.showRoute);
        namaPantai = findViewById(R.id.namaPantai);
        deskripsiPantai = findViewById(R.id.deskripsiPantai);
        jarakPantai = findViewById(R.id.jarakPantai);
        alamatPantai = findViewById(R.id.alamatPantai);
        fasilitasPantai = findViewById(R.id.fasilitasPantai);
        transportasiPantai = findViewById(R.id.transportasiPantai);
        ratingPantai = findViewById(R.id.ratingPantai);
        gambarPantai = findViewById(R.id.gambarPantai);
        htmPantai = findViewById(R.id.htmPantai);

        //get data from intent
        Intent intent = getIntent();
        latitude=intent.getExtras().getString("latitude");
        longitude=intent.getExtras().getString("longitude");
        valueNamaPantai=intent.getExtras().getString("nama_pantai");
        valueJarak=intent.getExtras().getString("jarak");
        valueRating=intent.getExtras().getString("rating");
        valueFasilitas=intent.getExtras().getString("fasilitas");
        valueTransportasi=intent.getExtras().getString("transportasi");
        myLatitude = intent.getExtras().getString("myLatitude");
        myLongitude = intent.getExtras().getString("myLongitude");
        valueBiayaMasuk = intent.getExtras().getString("biaya_masuk");
        image = intent.getExtras().getString("image");
        deskripsi = intent.getExtras().getString("deskripsi");
        alamat = intent.getExtras().getString("alamat");
        Log.e("Transportas", valueTransportasi);

        //set texview and rating
        namaPantai.setText(valueNamaPantai);
        deskripsiPantai.setText(deskripsi);
        jarakPantai.setText(valueJarak+" m");
        String domain = getResources().getString(R.string.url);
        String url = domain+"image/"+image+".jpg";
        Picasso.get().load(url).into(gambarPantai);
//        alamatPantai.setText(valueNamaPantai);
        fasilitasPantai.setText(valueFasilitas);
        transportasiPantai.setText(valueTransportasi);
        ratingPantai.setRating(Float.valueOf(valueRating));
        alamatPantai.setText(alamat);
        htmPantai.setText("Rp. "+valueBiayaMasuk);

        //show Route button
        showRoute.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
//                Intent myIntent = new Intent(DetailPantaiActivity.this, MapsActivity.class);
//                myIntent.putExtra("nama_pantai", valueNamaPantai);
//                myIntent.putExtra("biaya_masuk", valueBiayaMasuk);
//                myIntent.putExtra("transportasi", valueTransportasi);
//                myIntent.putExtra("fasilitas", valueFasilitas);
//                myIntent.putExtra("jarak", valueJarak);
//                myIntent.putExtra("longitude", longitude);
//                myIntent.putExtra("latitude", latitude);
//                myIntent.putExtra("rating", valueRating);
//                myIntent.putExtra("myLatitude", myLatitude);
//                myIntent.putExtra("myLongitude", myLongitude);
//                startActivity(myIntent);
                final AlertDialog.Builder builder = new AlertDialog.Builder(DetailPantaiActivity.this);
                builder.setMessage("Open Google Maps?")
                        .setCancelable(true)
                        .setPositiveButton("yes", new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialogInterface, int i) {
                                Uri gmmIntentUri = Uri.parse("google.navigation:q=" + latitude + "," + longitude);
                                Intent mapIntent = new Intent(Intent.ACTION_VIEW, gmmIntentUri);
                                mapIntent.setPackage("com.google.android.apps.maps");

                                try{
                                    startActivity(mapIntent);
                                }catch (NullPointerException e){
                                    Log.e("Google Maps", "onClick: NullPointerException: Couldn't open map." + e.getMessage() );
                                    Toast.makeText(DetailPantaiActivity.this, "Couldn't open map", Toast.LENGTH_SHORT).show();
                                }
                            }
                        })
                        .setNegativeButton("no", new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialogInterface, int i) {
                                    dialogInterface.cancel();
                            }
                        });
                final AlertDialog alert = builder.create();
                alert.show();
            }
        });
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;

        // Add a marker in Sydney and move the camera
        LatLng markerPantai = new LatLng(Double.valueOf(latitude), Double.valueOf(longitude));
        mMap.addMarker(new MarkerOptions().position(markerPantai).title(valueNamaPantai));
        mMap.moveCamera(CameraUpdateFactory.newLatLng(markerPantai));
        mMap.moveCamera(CameraUpdateFactory.zoomTo(13));
    }
}
