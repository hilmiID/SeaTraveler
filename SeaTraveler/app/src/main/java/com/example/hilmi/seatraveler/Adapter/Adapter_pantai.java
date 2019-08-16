package com.example.hilmi.seatraveler.Adapter;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.media.Rating;
import android.support.annotation.NonNull;
import android.support.v7.widget.CardView;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.RatingBar;
import android.widget.TextView;

import com.example.hilmi.seatraveler.Activity.DetailPantaiActivity;
import com.example.hilmi.seatraveler.Model.pantai;
import com.example.hilmi.seatraveler.R;
import com.squareup.picasso.Picasso;
import com.squareup.picasso.Target;

import java.util.ArrayList;

public class Adapter_pantai extends RecyclerView.Adapter<Adapter_pantai.NotifViewHolder> {
    private ArrayList<pantai> datalist;
    private Context context;

    public Adapter_pantai(Context context, ArrayList<pantai> datalist){
        this.datalist=datalist;
        this.context=context;
    }
    @Override
    public NotifViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        LayoutInflater layoutInflater = LayoutInflater.from(parent.getContext());
        View view = layoutInflater.inflate(R.layout.row_pantai,parent,false);

        return new NotifViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull NotifViewHolder holder, final int position) {
        holder.namaPantai.setText(datalist.get(position).getNama_pantai());
        holder.highlight.setText("Merupakan "+datalist.get(position).getNama_pantai());
        holder.ratingPantai.setRating(Float.parseFloat(datalist.get(position).getRating()));
        String domain = context.getString(R.string.url);
        String url = domain+"image/"+datalist.get(position).getImage()+".jpg";

        Picasso.get().load(url).into(holder.gambarPantai);

        holder.cv.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Activity activity = (Activity) v.getContext();
                Intent myIntent = new Intent(context, DetailPantaiActivity.class);
                myIntent.putExtra("nama_pantai", datalist.get(position).getNama_pantai());
                myIntent.putExtra("biaya_masuk", datalist.get(position).getBiaya_masuk());
                myIntent.putExtra("transportasi", datalist.get(position).getTransportasi());
                myIntent.putExtra("fasilitas", datalist.get(position).getFasilitas());
                myIntent.putExtra("jarak", datalist.get(position).getJarak());
                myIntent.putExtra("longitude", datalist.get(position).getLongitude());
                myIntent.putExtra("latitude", datalist.get(position).getLatitude());
                myIntent.putExtra("rating", datalist.get(position).getRating());
                myIntent.putExtra("myLatitude", datalist.get(position).getMyLatitude());
                myIntent.putExtra("myLongitude", datalist.get(position).getMyLongitude());
                myIntent.putExtra("image", datalist.get(position).getImage());
                myIntent.putExtra("deskripsi", datalist.get(position).getDeskripsi());
                myIntent.putExtra("alamat", datalist.get(position).getAlamat());
                context.startActivity(myIntent);
            }
        });
    }

    @Override
    public int getItemCount() {
        return (datalist != null) ? datalist.size() : 0;
    }


    public class NotifViewHolder extends RecyclerView.ViewHolder{
        private TextView namaPantai, highlight;
        private ImageView gambarPantai;
        private RatingBar ratingPantai;
        private View mview;
        private CardView cv;
        public NotifViewHolder(View itemview){
            super(itemview);
            cv = (CardView) itemView.findViewById(R.id.cv);
            namaPantai = (TextView) itemview.findViewById(R.id.namaPantai);
            highlight = (TextView) itemview.findViewById(R.id.highlight);
            gambarPantai = (ImageView) itemview.findViewById(R.id.gambarPantai);
            ratingPantai = (RatingBar) itemview.findViewById(R.id.ratingPantai);
        }
    }
}
